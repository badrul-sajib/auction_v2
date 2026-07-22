<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\AllowedIp;
use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class AllowedIpController extends Controller
{
    public function index()
    {
        $ips = AllowedIp::latest()->paginate(20);
        $featureEnabled = AllowedIp::featureEnabled();

        return view('pages.inventory.allowed-ips.index', compact('ips', 'featureEnabled'));
    }

    public function toggleFeature()
    {
        $enabled = ! AllowedIp::featureEnabled();
        Setting::put(AllowedIp::FEATURE_KEY, $enabled ? '1' : '0');

        return back()->with('status', 'IP whitelist ' . ($enabled ? 'enabled' : 'disabled') . '.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'value' => ['required', 'string', 'max:64', $this->ipOrCidrRule()],
            'label' => ['required', 'string', 'max:255'],
        ]);

        AllowedIp::create([
            'value' => trim($data['value']),
            'label' => $data['label'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('inventory.allowed-ips.index')->with('status', 'IP added to whitelist.');
    }

    public function update(Request $request, AllowedIp $allowedIp)
    {
        $data = $request->validate([
            'value' => ['required', 'string', 'max:64', $this->ipOrCidrRule()],
            'label' => ['required', 'string', 'max:255'],
        ]);

        $allowedIp->update([
            'value' => trim($data['value']),
            'label' => $data['label'] ?? null,
        ]);

        return redirect()->route('inventory.allowed-ips.index')->with('status', 'IP updated.');
    }

    public function toggle(AllowedIp $allowedIp)
    {
        $allowedIp->update(['is_active' => ! $allowedIp->is_active]);

        return back()->with('status', 'IP ' . ($allowedIp->is_active ? 'enabled' : 'disabled') . '.');
    }

    public function destroy(AllowedIp $allowedIp)
    {
        $allowedIp->delete();

        return redirect()->route('inventory.allowed-ips.index')->with('status', 'IP removed from whitelist.');
    }

    /** Validation rule accepting a single IP or a CIDR range (v4/v6). */
    private function ipOrCidrRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            $value = trim((string) $value);

            if (filter_var($value, FILTER_VALIDATE_IP) !== false) {
                return;
            }

            if (str_contains($value, '/')) {
                [$ip, $prefix] = explode('/', $value, 2);
                $isV4 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
                $isV6 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;

                if (($isV4 || $isV6) && ctype_digit($prefix)) {
                    $max = $isV4 ? 32 : 128;
                    if ((int) $prefix >= 0 && (int) $prefix <= $max) {
                        return;
                    }
                }
            }

            $fail('Enter a valid IP address or CIDR range (e.g. 203.0.113.5 or 203.0.113.0/24).');
        };
    }
}
