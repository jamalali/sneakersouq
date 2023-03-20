<script setup>
import { computed, ref, reactive } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import * as _ from 'lodash-es'

const state = reactive({
    syncMessage: null
})

const props = defineProps({
    sneakers: Array,
    cacheKey: String,
    page: Number,
    sku: String
})

const nextPage = computed(() => props.page + 1)
// const selectedSneakers = ref([])

const rowClass = 'border-b border-slate-100 p-4 pl-8'

const searchForm = useForm({
    sku: props.sku
})

function handleSearch() {
    searchForm.get('/search-sneakers')
}

const syncForm = useForm({
    cacheKey: props.cacheKey,
    selectedSneakers: []
})

function handleSync() {
    syncForm.post('/search-sneakers', {
        onSuccess: page => {
            const numSucceeded = page.props.success
            const numFailed = page.props.fail

            state.syncMessage = `Shopify sync complete. ${numSucceeded} products added. ${numFailed} errors.`
        }
    })
}
</script>

<template>
    <AppLayout title="Search Sneakers">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Search Sneakers
            </h2>
            <p class="text-sm text-gray-500">
                Search for sneakers via the Sneaker Database API: <br />
                (https://rapidapi.com/tg4-solutions-tg4-solutions-default/api/the-sneaker-database)
            </p>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div>
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">

                            <p v-if="state.syncMessage" v-text="state.syncMessage" class="bg-sky-200 border border-gray-400 font-bold mb-2 p-2 rounded"></p>

                            <form class="flex-1 flex" @submit.prevent="handleSearch">
                                <input v-model="searchForm.sku" type="text" name="sku" placeholder="Product skus, seperated by commas (e.g. 555088-134,dj4375-106)" class="flex-1 py-1 px-4 text-sm border-gray-300 rounded-l-md shadow-sm" />
                                <button :disabled="searchForm.processing" class="button rounded-none rounded-r-md disabled:opacity-50" type="submit">Search</button>
                            </form>

                            <form class="flex-1 flex" @submit.prevent="handleSync">
                                <table v-if="sneakers" class="border-collapse border">
                                    <tbody>
                                        <tr v-for="sneaker in sneakers" :key="sneaker.id" class="border-b border-gray-200 even:bg-slate-50" :class="{ 'even:bg-sky-100 bg-sky-50': syncForm.selectedSneakers.includes(sneaker.id) }">
                                            <td class="p-2 text-center border-r">
                                                <input type="checkbox" v-model="syncForm.selectedSneakers" :value="sneaker.id" />
                                            </td>
                                            <td class="w-60 p-2">
                                                <a v-if="sneaker.image.original.length" :href="sneaker.image.original" target="_blank">
                                                    <img :src="sneaker.image.thumbnail" :alt="sneaker.name" />
                                                </a>

                                                <table class="border mb-2">
                                                    <tr :class="rowClass">
                                                        <th class="text-left p-1 text-xs">Colorway:</th>
                                                        <td class="p-1 text-xs whitespace-nowrap">{{ sneaker.colorway }}</td>
                                                    </tr>
                                                    <tr :class="rowClass">
                                                        <th class="text-left p-1 text-xs">Silhouette:</th>
                                                        <td class="p-1 text-xs whitespace-nowrap">{{ sneaker.silhouette }}</td>
                                                    </tr>
                                                    <tr :class="rowClass">
                                                        <th class="text-left p-1 text-xs whitespace-nowrap">Retail price:</th>
                                                        <td class="p-1 text-xs whitespace-nowrap">{{ sneaker.retailPrice }}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td class="p-2">
                                                <p class="font-bold text-gray-400">
                                                    {{ sneaker.brand }}
                                                </p>
                                                <h3 class="font-bold text-lg">
                                                    {{ sneaker.name }}
                                                </h3>
                                                <p class="mb-2">
                                                    SKU: {{ sneaker.sku }}
                                                </p>

                                                <p v-if="sneaker.story" v-html="sneaker.story" class="mb-2">
                                                </p>

                                                <template v-for="(linkVal, linkName) in sneaker.links" :key="linkName + '_' + linkVal">
                                                    <p>
                                                        {{ _.startCase(linkName) }}: <a class="hover:text-blue-600 hover:underline" :href="linkVal" target="_blank" v-text="linkVal"></a>
                                                    </p>
                                                </template>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <td colspan="3" class="p-2">
                                            <button type="submit" :disabled="syncForm.processing || syncForm.selectedSneakers.length==0" class="button rounded-md disabled:opacity-50">
                                                Sync to Shopify
                                            </button>
                                        </td>
                                    </tfoot>
                                </table>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>