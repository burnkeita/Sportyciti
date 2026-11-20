<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Services\Ads\AdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class AdController extends Controller
{
    /** Click-through tracking redirect. Only redirects to the URL stored on the ad (no open redirect). */
    public function click(Advertisement $advertisement, AdService $ads): RedirectResponse
    {
        $target = Str::sanitizeUrl($advertisement->target_url);

        abort_unless($advertisement->is_active && $target, 404);

        $ads->recordClick($advertisement);

        return redirect()->away($target);
    }
}
