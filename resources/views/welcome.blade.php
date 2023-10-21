<x-layout>
    <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
        <div v-if="canLogin" class="sm:fixed sm:top-0 sm:right-0 p-6 text-right">
            <template v-if="!$page.props.auth.user">
                <Link :href="route('login')" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log in</Link>
            </template>
        </div>

        <div class="max-w-7xl mx-auto p-6 lg:p-8">
            <div class="flex mb-1 justify-center">
                <img class="w-20" src="/assets/img/SneakerSouqLogo.jpeg" alt="SneakerSouq Logo">
            </div>
            <div class="flex font-bold justify-center">
                Admin
            </div>
        </div>
    </div>
</x-layout>