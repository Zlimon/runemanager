php_executable=$1
base_directory=$2
artifacts_path=$3

"$php_executable" artisan storage:link

"$php_executable" artisan config:cache

"$php_executable" artisan route:cache

"$php_executable" artisan view:cache

"$php_executable" artisan migrate --force

# Drop cached application data (the Hiscores nav lists, keyed on skills and
# bosses) so a release that changes them is visible immediately rather than
# when the day-long entries expire.
"$php_executable" artisan cache:clear

# Restore the bundled Default Vanilla pack (the icon baseline) if the persisted
# resource-packs dir doesn't have it yet. Best-effort — never fails the deploy.
"$php_executable" artisan resourcepack:ensure-vanilla || true

# Each pack holds its own copy of the per-pack CSS, taken when it was installed,
# and resource-packs is shared by symlink across releases — so a release that
# changes resources/css/per-pack.css reaches no installed pack without this.
# Runs after ensure-vanilla so a freshly restored Vanilla is covered too.
"$php_executable" artisan resourcepack:sync-css || true
