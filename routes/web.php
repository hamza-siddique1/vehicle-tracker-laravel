<?php

use App\Http\Controllers\CSVHeaderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatatableController;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\LocationsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RunListController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VinOcrController;
use App\Models\Vehicle;
use App\Models\VehicleMetas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */
Route::get('/clear_cache', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    dd('Cache Cleared');
});

Route::get('backup', function () {
    \Illuminate\Support\Facades\Artisan::call('backup:full');
    dd('Backup created.');
});

Route::get('/phpinfo', function () {
    echo phpinfo();
});


Route::redirect('/', '/dashboard');

Route::get('/reset', function () {
    #get query params
    $confirm = $_GET['confirm'];
    if ($confirm == 'true') {
        DB::table('vehicles')->truncate();
        DB::table('vehicle_metas')->truncate();
        DB::table('vehicle_notes')->truncate();

        Session::flash('success', 'Vehicle data reset successfully');
        return view('pages.reset.index');
    } else {
        dump('You need to confirm by adding \'confirm=true\' in the url');
    }

})->middleware('admin');

// Route::get('/reset-all', function () {

//     \Illuminate\Support\Facades\Artisan::call('migrate:fresh');
//     \Illuminate\Support\Facades\Artisan::call('db:seed');
//     \Illuminate\Support\Facades\Artisan::call('optimize:clear');

// });


Route::get('/migrate', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate');
});

Route::group(['middleware' => ['auth']], function () {


    //Viewer role can also see sold vehicles section
    Route::get('vehicles/sold', [VehicleController::class, 'sold_vehicles'])->name('vehicles.sold');

    Route::group([
        'prefix' => 'profile',
    ], function () {
        Route::get('/{tab?}', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('account', [ProfileController::class, 'account'])->name('profile.account');

    });

    //    Route::post('images', [ImageController::class, 'store'])->name('images.store');
    //    Route::post('images/feedback', [ImageController::class, 'store_feedback'])->name('images.store_feedback');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::get('api/v1/vehicles', [DatatableController::class, 'vehicles'])->name('vehicles.ajax');
    Route::get('api/v1/vehicles/sold', [DatatableController::class, 'vehicles_sold'])->name('vehicles.sold.ajax');
    //Render modal for vehicle details
    Route::get('vehicles/{vehicle}/html', [DatatableController::class, 'getVehicleDetails'])->name('vehicle.detail.html');

    Route::impersonate();

    Route::group(
        ['middleware' => 'admin',
        ],
        function () {
            //Route::get('logs', [LogViewerController::class, 'index']);
            Route::post('/process-csv', [CSVHeaderController::class,'processCsv'])->name('process.csv');

            Route::post('/field-mapping/save', [CSVHeaderController::class,'saveFieldMapping'])->name('field.mapping.save');

            Route::post('field-mapping', [CSVHeaderController::class, 'showFieldMapping'])->name('field.mapping');
            Route::resource('headers', CSVHeaderController::class);

            //        Route::post('csv/sell', [VehicleController::class, 'import_sale_csv'])->name('csv.sale');
            //        Route::post('csv/inventory', [VehicleController::class, 'import_inventory_csv'])->name('csv.inventory');

            Route::resource('users', UsersController::class);
            Route::post('password/{user}', [UsersController::class, 'password_update'])->name('user.password_update');

        }
    );

    Route::group(['middleware' => 'vehicle_manager'], function () {

    });

    Route::group(['middleware' => 'yard_manager'], function () {
        Route::get('vehicles/upload/buy', [VehicleController::class, 'create_upload_buy'])->name('upload.create.buy');
        Route::get('vehicles/upload/inventory', [VehicleController::class, 'create_upload_inventory'])->name('upload.create.inventory');
        Route::get('vehicles/upload/sold', [VehicleController::class, 'create_upload_sold'])->name('upload.create.sold');

        /**
         * Run List
         */
        Route::get('runlist/upload', [RunListController::class, 'create_upload_run_list'])->name('upload.create.runlist');
        Route::get('runlists', [RunListController::class, 'index'])->name('vehicles.runlists.index');


        Route::post('buy/copart', [VehicleController::class, 'import_buy_copart_csv'])->name('buy.copart');
        Route::post('buy/iaai', [VehicleController::class, 'import_buy_iaai_csv'])->name('buy.iaai');
        Route::post('inventory/copart', [VehicleController::class, 'import_inventory_copart_csv'])->name('inventory.copart');
        Route::post('sold/copart', [VehicleController::class, 'import_sold_copart_csv'])->name('sold.copart');
        Route::post('vehicle/runlist', [RunListController::class, 'import_run_list_csv'])->name('vehicles.runlist');

        Route::get('vehicle/location/{location}', [LocationsController::class, 'add_new_location'])->name('location.add');

        Route::delete('vehicles/delete-multiple', [VehicleController::class, 'delete_multiple_vehicles'])->name('vehicles.delete-multiple');
        Route::resource('vehicles', VehicleController::class)->except('index');
        Route::resource('locations', LocationsController::class);

        Route::get('/vinocr/form', [VinOcrController::class, 'showForm'])->name('vinocr.showform');
        Route::post('/vinocr/process', [VinOcrController::class, 'processImage'])->name('vinocr.process');
        Route::patch('/vinocr/{vehicle}/detail1', [VinOcrController::class, 'update_detail_1'])->name('vinocr.update.detail1');
        Route::patch('/vinocr/detail2', [VinOcrController::class, 'update_detail_2'])->name('vinocr.update.detail2');
    });

    Route::resource('vehicles', VehicleController::class)->only('index');

});

Route::get('next_vehicle_id', [DatatableController::class, 'next_vehicle_id']);
Route::get('delete_unsaved_vehicles', [VehicleController::class, 'delete_unsaved_vehicles']);

//Elections
Route::get('elections', [ElectionController::class, 'index']);
Route::get('update_election', [ElectionController::class, 'update_election']);

//Guru
Route::get('get-token', [GuruController::class, 'getAccessToken']);
Route::get('refresh-token', [GuruController::class, 'getNewAccessTokenFromRefreshToken']);
Route::get('store-jobs', [GuruController::class, 'store_jobs']);



Route::get('get_run_lists', [RunListController::class, 'get_run_lists'])->name('runlists.filter');
Route::get('export_run_list', [RunListController::class, 'export_run_list'])->name('runlist.export');


Route::get('duplicate/vehicles', [VehicleController::class, 'duplicate_vehicles']);

Route::get('/debug', [App\Http\Controllers\TelescopeSearchController::class, 'index'])->middleware('admin');

Route::get('/fetch-copart-lot/{lotNumber}', [VehicleController::class, 'check_status']);

Route::view('/iframe', 'iframe');//
