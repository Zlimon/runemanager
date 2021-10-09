<template>
    <div class="row mb-3">
        <div class="col-3 d-none d-md-block">
            <div class="bg-dark background-dialog-panel p-3">
                <h2 class="text-center header-chatbox-sword">Notifications</h2>
                <announcementall></announcementall>
            </div>
        </div>

        <div class="col">
            <div class="bg-dark background-dialog-panel p-3">
                <div class="d-flex justify-content-between mb-3">
                    <div class="col-9 d-flex">
                        <img :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + user.icon_id + '.png'"
                             class="pixel"
                             alt="Profile icon"
                             style="width: 7.5rem; height: 7.5rem;">

                        <div>
                            <h1>Welcome, {{ user.name }}</h1>
                            <p>Joined: <strong>{{ user.created_at | moment('ddd. Do MMM HH:mm') }}</strong></p>
                        </div>
                    </div>

                    <p><a :href="'/user/edit'">Edit user</a></p>
                </div>

                <div class="d-flex mb-3">
                    <div class="col-md-6">
                        <div class="background-dialog-iron-rivets px-4 pt-1">
                            <h3 class="text-center">Accounts</h3>

                            <hr>

                            <div v-for="account in user.account" class="row align-items-center">
                                <div class="col-md-5">
                                    <p>
                                        <img v-if="account.account_type !== 'normal'"
                                             :src="'images/' + account.account_type +'.png'"
                                             class="pixel"
                                             alt="Account type icon"
                                             style="width: 1rem;">
                                        <strong>
                                            <a :href="'/account/' + account.username + '/show'">{{ account.username }}</a>
                                        </strong>
                                    </p>
                                </div>

                                <div class="col-md-4">
                                    <span>Total level:</span>
                                    <br>
                                    <strong>{{ account.level }}</strong>
                                </div>

                                <div v-if="account.online === 1" class="col-md-2">
                                    <div class="btn btn-danger">Log out</div>
                                </div>

                                <hr>
                            </div>

<!--                            @foreach ($user->authStatus as $account)-->
<!--                                @if ($loop->first)-->
<!--                                    <h3 class="text-center">Account Auth Status</h3>-->

<!--                                    <hr>-->
<!--                                @endif-->

<!--                                <div class="row align-items-center">-->
<!--                                    <div class="col-md-5">-->
<!--                                        <p>-->
<!--                                            @if ($account->account_type !== "normal")-->
<!--                                                <img src="{{ asset('images/'.$account->account_type.'.png') }}"-->
<!--                                                     class="pixel"-->
<!--                                                     alt="{{ Helper::formatAccountTypeName($account->account_type) }} icon"-->
<!--                                                     style="width: 1rem;">-->
<!--                                            @endif-->
<!--                                            <strong>-->
<!--                                                <a href="{{ route('account-show', $account->username) }}">{{ $account->username }}</a>-->
<!--                                            </strong>-->
<!--                                        </p>-->
<!--                                    </div>-->

<!--                                    <div class="col-md-4">-->
<!--                                        <span>Status:</span>-->
<!--                                        <br>-->
<!--                                        <span><strong>{{ ucfirst($account->status) }}</strong></span>-->
<!--                                    </div>-->

<!--                                    <div class="col-md-2">-->
<!--                                        <form method="POST" action="{{ route('account-auth-delete') }}">-->
<!--                                            @csrf-->
<!--                                            @method('DELETE')-->

<!--                                            <button type="submit" class="btn btn-danger">Delete</button>-->
<!--                                        </form>-->
<!--                                    </div>-->
<!--                                </div>-->

<!--                                <hr>-->
<!--                            @endforeach-->

                            <div class="text-center">
                                <a :href="'/account/create'">
                                    <div class="btn btn-lg button-combat-style-thin">
                                        <span>Link account</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-for="account in user.account" class="row align-items-center">
                    <div class="py-2" style="clear: both;"></div>
                    <div class="row">
                        <div class="col-md-8">
                            <accounthiscore :account="account"></accounthiscore>
                        </div>

                        <div class="col-md-4">
<!--                            <accountnotification :account="{{ $account }}"></accountnotification>-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
require('moment');

export default {
    name: "PageHome",

    props: {
        user: {required: true},
    },
}
</script>

<style scoped>

</style>
