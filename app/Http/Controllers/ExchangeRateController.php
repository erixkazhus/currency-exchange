<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\ExchangeRate;

class ExchangeRateController extends Controller
{
    public function getRates(Request $request)
    {
        $apiKey = $request->input('apiKey');
        $currency = strtoupper($request->input('target_currency', 'USD'));
        $perPage = 10;
        
        if (!$apiKey) {
            return response()->json(['error' => 'API key is required'], 400);
        }
        $client = new Client();

        try {
            $response = $client->request('GET', 'https://anyapi.io/api/v1/exchange/rates', [
                'query' => [
                    'apiKey' => $apiKey,
                    'base' => 'EUR',
                ],
            ]);
            
            $data = json_decode($response->getBody()->getContents(), true);
            
            $existingEntry = ExchangeRate::where('date', now()->toDateString())->first();
            if (!$existingEntry) {
                ExchangeRate::create([
                    'date' => now()->toDateString(),
                    'usd_rate' => $data['rates']['USD'],
                    'gbp_rate' => $data['rates']['GBP'],
                    'aud_rate' => $data['rates']['AUD'],
                ]);
            }
            
            $rates = ExchangeRate::orderBy('date', 'desc')->paginate($perPage);
            
            $filteredRates = $rates->map(function ($rate) use ($currency) {
                return [
                    'date' => $rate->date,
                    'rate' => $rate->{strtolower($currency) . '_rate'},
                ];
            });
            
            $ratesArray = $filteredRates->pluck('rate')->toArray();
          
            $min = min($ratesArray);
            $max = max($ratesArray);
            $avg = array_sum($ratesArray) / count($ratesArray);

            return response()->json([
                'rates' => $filteredRates,
                'min' => $min,
                'max' => $max,
                'avg' => $avg,
                'currency' => $currency,
                'total' => $rates->total(),
                'current_page' => $rates->currentPage(),
                'last_page' => $rates->lastPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }
}

