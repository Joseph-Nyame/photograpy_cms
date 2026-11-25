<?php

use App\Support\DefaultServicePackages;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_packages') || ! Schema::hasTable('service_package_items')) {
            return;
        }

        foreach (DefaultServicePackages::all() as $slug => $packages) {
            $service = DB::table('services')->where('slug', $slug)->first();

            if ($service === null || DB::table('service_packages')->where('service_id', $service->id)->exists()) {
                continue;
            }

            foreach (array_values($packages) as $sort => $package) {
                $packageId = DB::table('service_packages')->insertGetId([
                    'service_id' => $service->id,
                    'name' => $package['name'],
                    'sort' => $sort,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach (array_values($package['items']) as $itemSort => $item) {
                    DB::table('service_package_items')->insert([
                        'service_package_id' => $packageId,
                        'label' => $item['label'],
                        'value' => $item['value'],
                        'sort' => $itemSort,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('service_packages') || ! Schema::hasTable('service_package_items')) {
            return;
        }

        foreach (array_keys(DefaultServicePackages::all()) as $slug) {
            $service = DB::table('services')->where('slug', $slug)->first();

            if ($service === null) {
                continue;
            }

            $packageIds = DB::table('service_packages')->where('service_id', $service->id)->pluck('id');

            DB::table('service_package_items')->whereIn('service_package_id', $packageIds)->delete();
            DB::table('service_packages')->where('service_id', $service->id)->delete();
        }
    }
};
