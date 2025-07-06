<?php

use Illuminate\Support\Facades\Route;
use App\Jobs\UpdateOrderStatusJob;

Route::get('/test-dispatch', function () {
    dispatch(new UpdateOrderStatusJob());
    return '✅ UpdateOrderStatusJob dispatched!';
});
