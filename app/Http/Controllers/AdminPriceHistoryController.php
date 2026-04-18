<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PriceHistory;

class AdminPriceHistoryController extends Controller
{
    private function checkAdmin(): void
    {
        if (!Auth::check() || Auth::user()->role !== '1') {
            abort(403, 'Accesso negato');
        }
    }

    public function index()
    {
        if (Auth::user()->role !== '1') {
            return redirect('/home')->with('error', 'Accesso negato');
        }
        return view('admin.price-history.index');
    }

    public function topVariations(Request $request)
    {
        $this->checkAdmin();
        return response()->json(['data' => [], 'insufficient_data' => true]);
    }

    public function search(Request $request)
    {
        $this->checkAdmin();
        return response()->json(['data' => []]);
    }

    public function departureHistory(Request $request, string $departureId)
    {
        $this->checkAdmin();
        return response()->json(['departure' => null, 'series' => []]);
    }
}
