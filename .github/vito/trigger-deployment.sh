#!/usr/bin/env bash
# Triggers a VitoDeploy deployment for one site and waits until it finishes or fails.
#
# Required env: VITO_URL, VITO_API_TOKEN, VITO_PROJECT_ID, VITO_SERVER_ID, VITO_SITE_ID
# Optional env: VITO_TIMEOUT (seconds, default 900), VITO_POLL_INTERVAL (seconds, default 10),
#   EXPECTED_COMMIT (fail unless Vito deployed exactly this commit),
#   VITO_ALLOW_HTTP=1 (allow a plain http VITO_URL; http is otherwise only allowed for localhost),
#   CF_ACCESS_CLIENT_ID + CF_ACCESS_CLIENT_SECRET (Cloudflare Access service token guarding the Vito API)
set -euo pipefail

for name in VITO_URL VITO_API_TOKEN VITO_PROJECT_ID VITO_SERVER_ID VITO_SITE_ID; do
    if [ -z "${!name:-}" ]; then
        echo "::error::${name} is not set." >&2
        exit 1
    fi
done

# The API token is sent with every request, so never over plain http to a remote host.
if [[ "$VITO_URL" != https://* && ! "$VITO_URL" =~ ^http://(127\.0\.0\.1|localhost)(:[0-9]+)?(/.*)?$ ]]; then
    if [[ "$VITO_URL" != http://* || "${VITO_ALLOW_HTTP:-}" != "1" ]]; then
        echo "::error::VITO_URL must be an https:// URL (set VITO_ALLOW_HTTP=1 to allow http)." >&2
        exit 1
    fi
fi

timeout="${VITO_TIMEOUT:-900}"
interval="${VITO_POLL_INTERVAL:-10}"
endpoint="${VITO_URL%/}/api/projects/${VITO_PROJECT_ID}/servers/${VITO_SERVER_ID}/sites/${VITO_SITE_ID}/deployments"

workdir="$(mktemp -d)"
trap 'rm -rf "$workdir"' EXIT

# Keep the token out of the process list by passing headers through a file.
(umask 077 && printf 'Authorization: Bearer %s\nAccept: application/json\n' "$VITO_API_TOKEN" > "$workdir/headers")

# Vito sits behind Cloudflare Access; the service token lets this runner through.
if [ -n "${CF_ACCESS_CLIENT_ID:-}" ] || [ -n "${CF_ACCESS_CLIENT_SECRET:-}" ]; then
    if [ -z "${CF_ACCESS_CLIENT_ID:-}" ] || [ -z "${CF_ACCESS_CLIENT_SECRET:-}" ]; then
        echo "::error::Set both CF_ACCESS_CLIENT_ID and CF_ACCESS_CLIENT_SECRET, or neither." >&2
        exit 1
    fi
    printf 'CF-Access-Client-Id: %s\nCF-Access-Client-Secret: %s\n' \
        "$CF_ACCESS_CLIENT_ID" "$CF_ACCESS_CLIENT_SECRET" >> "$workdir/headers"
fi

# request METHOD URL [curl args...]: writes the body to $workdir/body and prints the HTTP status.
request() {
    local method="$1" url="$2"
    shift 2
    rm -f "$workdir/body"
    curl --silent --show-error --max-time 30 --request "$method" \
        --header "@$workdir/headers" --output "$workdir/body" --write-out '%{http_code}' \
        "$@" "$url" || true
}

fail_response() {
    echo "::error::$1 (HTTP $2)" >&2
    head -c 500 "$workdir/body" >&2 2>/dev/null || true
    echo >&2
    exit 1
}

# Vito returns the Deployment resource unwrapped; also accept a {"data": ...} wrapper.
field() {
    jq -r "(.data // .) | .$1 // empty" "$workdir/body" 2>/dev/null || true
}

status_code="$(request POST "$endpoint")"
if [[ "$status_code" != 2* ]]; then
    fail_response "Failed to start deployment" "$status_code"
fi

deployment_id="$(field id)"
if [ -z "$deployment_id" ]; then
    fail_response "Deployment response has no id" "$status_code"
fi

echo "Started Vito deployment ${deployment_id} (commit $(field commit_id_short))."

deadline=$((SECONDS + timeout))
while true; do
    status="$(field status)"

    case "$status" in
        finished)
            # Vito deploys the branch head, which may have moved past the commit CI tested.
            deployed_commit="$(field commit_id)"
            if [ -n "${EXPECTED_COMMIT:-}" ] && [ "$deployed_commit" != "$EXPECTED_COMMIT" ]; then
                echo "::error::Deployment ${deployment_id} deployed commit '${deployed_commit:-unknown}', not the tested ${EXPECTED_COMMIT}. A newer, untested commit is live; check it and redeploy." >&2
                exit 1
            fi
            echo "Deployment ${deployment_id} finished."
            exit 0
            ;;
        failed)
            echo "::error::Deployment ${deployment_id} failed. See the deployment log in Vito." >&2
            exit 1
            ;;
    esac

    if [ "$SECONDS" -ge "$deadline" ]; then
        echo "::error::Deployment ${deployment_id} still '${status}' after ${timeout}s." >&2
        exit 1
    fi

    sleep "$interval"

    status_code="$(request GET "${endpoint}/${deployment_id}" --retry 3 --retry-delay 5)"
    if [[ "$status_code" != 2* ]]; then
        fail_response "Failed to fetch deployment ${deployment_id}" "$status_code"
    fi

    echo "Deployment ${deployment_id}: $(field status)"
done
