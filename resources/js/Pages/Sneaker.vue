<script setup>
    import { computed, onMounted } from 'vue'
    import { router } from '@inertiajs/vue3'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import * as _ from 'lodash-es'

    const props = defineProps({
        sneaker: Object,
        cacheKey: String,
        shopProduct: Object
    })

    onMounted(() => {
        // console.log(props.shopProduct)
    })

    const sneakerProperties =  [
        'brand',
        'colorway',
        'estimatedMarketValue',
        'gender',
        'releaseDate',
        'releaseYear',
        'retailPrice',
        'silhouette',
        'sku'
    ]

    const shopProductProperties =  [
        'id',
        'title',
        'body_html',
        'created_at',
        'handle',
        'product_type',
        'vendor',
        'tags'
    ]

    const rowClass = computed(() => 'border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400')

    function fetchShopifyData() {
        router.post('/sneaker/shopify-up', {
            ...props.sneaker,
            'cacheKey': props.cacheKey
        })
    }
</script>

<template>
    <AppLayout title="Sneaker">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight" v-text="sneaker.name">
            </h2>
            <p class="text-sm text-gray-500" v-if="sneaker.story" v-html="sneaker.story">
            </p>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div>
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">

                            <div class="grid grid-flow-col">
                                <div class="col-span-1">
                                    <a v-if="sneaker.image.original.length" :href="sneaker.image.original" target="_blank">
                                        <img :src="sneaker.image.thumbnail" :alt="sneaker.name" />
                                    </a>
                                </div>
                                <div class="col-span-5">
                                    <h2 class="text-lg font-bold mb-1">
                                        Details
                                    </h2>
                                    <table class="border-collapse border mb-3">
                                        <tr v-for="propName in sneakerProperties" :key="propName + '_' + sneaker[propName]" :class="rowClass">
                                            <th class="text-left p-1 border-r">
                                                {{ _.startCase(propName) }}
                                            </th>
                                            <td class="p-1" v-html="sneaker[propName]"></td>
                                        </tr>
                                    </table>

                                    <h2 class="text-lg font-bold mb-1">
                                        Links
                                    </h2>
                                    <table class="border-collapse border mb-3">
                                        <tr v-for="(linkVal, linkName) in sneaker.links" :key="linkName + '_' + linkVal" :class="rowClass">
                                            <th class="text-left p-1 border-r">
                                                {{ _.startCase(linkName) }}
                                            </th>
                                            <td class="p-1">
                                                <a class="hover:text-blue-600 hover:underline" :href="linkVal" target="_blank" v-text="linkVal"></a>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <hr class="my-3" />

                            <div class="grid grid-flow-col">
                                <div class="col-span-1">
                                    <h2 class="text-lg font-bold mb-1">
                                        Shopify product
                                    </h2>
                                </div>
                                <div class="col-span-1 text-right">
                                    <form v-if="shopProduct==null" @submit.prevent="fetchShopifyData">
                                        <button class="button" type="submit">Sync to Shopify</button>
                                    </form>
                                    <div v-else>
                                        <a class="button mr-1" :href="shopProduct.adminUrl" target="_blank">
                                            View in Shopify admin
                                        </a>
                                        <a class="button" :href="shopProduct.storeUrl" target="_blank">
                                            View on Storefront
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <table v-if="shopProduct!=null" class="border-collapse border mb-3">
                                <tr v-for="propName in shopProductProperties" :key="propName + '_' + shopProduct[propName]" :class="rowClass">
                                    <th class="text-left p-1 border-r whitespace-nowrap">
                                        {{ _.startCase(propName) }}
                                    </th>
                                    <td class="p-1" v-html="shopProduct[propName]"></td>
                                </tr>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>