<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ProcessIncomingProduct;
use Illuminate\Support\Facades\Log;

class AgentsController extends Controller
{
    public function index(Request $request): View
    {
        $agents = array();

        $agents[] = array(
            'title' => 'Kicks Crew - Nike Products',
            'id' => 'g9af3diwy7'
        );

        $agents[] = array(
            'title' => 'Kicks Crew - Supreme Products',
            'id' => 'wlikgfpt0e'
        );
 
        return view('agents.list', [
            'agents' => $agents
        ]);
    }

    public function show(Request $request, $agentId): View
    {
        $agentyConfig = config('sneakersouq.agenty');
        $apiKey = $agentyConfig['api_key'];
        $endpoint = 'https://api.agenty.com/v2/results/'. $agentId;

        $params = array(
            'apikey' => $apiKey,
            'offset' => 0,
            'limit' => 1000
        );

        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->get($endpoint, $params);

        $agentDetails = $response->json();
        $total = $agentDetails['total'];

        return view('agents.show', [
            'agentId' => $agentId,
            'total' => $total
        ]);
    }

    public function sync(Request $request, $agentId)
    {
        $allResults = [];

        $limit = 1000;
        $page = 1;

        // "total" => 5000
        // "limit" => 500
        // "offset" => 0
        // "returned" => 500

        $firstPage = $this->getAgentResults($agentId, $page, $limit);
        $firstPageResult = $firstPage['result'];
        $allResults = array_merge($allResults, $firstPageResult);
        $total = $firstPage['total'];
        $page++;

        while (count($allResults) < $total) {
            $response = $this->getAgentResults($agentId, $page, $limit);
            $result = $response['result'];
            $allResults = array_merge($allResults, $result);

            if ($page == 10) {
                break;
            }

            $page++;
        }

        Log::info('Fetched ' . count($allResults) . ' products from Agenty');

        $this->parseAndQueueProducts($allResults);

        return redirect()->route('agents.show', [
            'agentId' => $agentId
        ]);
    }

    protected function parseAndQueueProducts($results)
    {
        foreach($results as $result) {
            $productContent = $result['Product'];
            $product = json_decode($productContent);

            unset($product->id);
            unset($product->created_at);
            unset($product->updated_at);
            unset($product->published_at);
            unset($product->handle);

            data_forget($product, 'variants.*.id');
            data_forget($product, 'variants.*.product_id');
            data_forget($product, 'variants.*.created_at');
            data_forget($product, 'variants.*.updated_at');

            data_forget($product, 'options.*.id');
            data_forget($product, 'options.*.product_id');

            data_forget($product, 'images.*.id');
            data_forget($product, 'images.*.product_id');
            data_forget($product, 'images.*.created_at');
            data_forget($product, 'images.*.updated_at');
            data_forget($product, 'images.*.variant_ids');

            $cacheKey = 'incoming_product_' . $product->title;
            Cache::put($cacheKey, $product);

            ProcessIncomingProduct::dispatch($cacheKey);
        }

        return true;
    }

    protected function getAgentResults($agentId, $page = 1, $limit = 1000)
    {
        $agentyConfig = config('sneakersouq.agenty');
        $apiKey = $agentyConfig['api_key'];
        $endpoint = 'https://api.agenty.com/v2/results/'. $agentId;

        $offset = 0;

        if ($page > 1) {
            $offset = $limit * ($page - 1);
        }

        $params = array(
            'apikey' => $apiKey,
            'offset' => $offset,
            'limit' => $limit
        );

        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->get($endpoint, $params);

        return $response->json();
    }
}