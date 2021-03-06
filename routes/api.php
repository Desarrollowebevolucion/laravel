<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'api'], function ($router) {
    Route::get('langlist', 'LocaleController@getLangList');
    Route::post('menu', 'MenuController@index');
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('register', 'AuthController@register'); 
    Route::resource('notes', 'NotesController');
    Route::resource('resource/{table}/resource', 'ResourceController');
   Route::post('users/getroles','UsersController@getRoles');
    Route::post('users/changerole','UsersController@changeRole');
    Route::post('users/setlang','UsersController@setLang');

    Route::group(['prefix' => '/user'], function () {

    Route::post('lockuser', 'UsersController@lockuser');
    Route::post('yourrequest', 'UsersController@yourrequest');
    Route::post('aceptrequest', 'UsersController@aceptrequest');
    Route::post('lockuserrequest', 'UsersController@lockuserrequest');
    Route::post('resetpassword', 'UsersController@resetpassword');
    Route::post('lockadmin', 'UsersController@lockadmin');
    Route::post('unlockadmin', 'UsersController@unlockadmin');

    
    Route::post('cancelrequestin', 'UsersController@cancelrequestin');
    Route::post('cancelrequest', 'UsersController@cancelrequest');
    Route::post('allusers', 'UsersController@getallusers');
    Route::post('unlockuser', 'UsersController@unlockuser');
    Route::post('createorrequest', 'UsersController@createorrequest');
    Route::post('restore','UsersController@restoreuser');
    Route::post('create','UsersController@created');
    Route::post('updatedesdeadmin', 'UsersController@updatedesdeadmin');
    Route::post('update','UsersController@update');
    Route::post('pruebaimagen','UsersController@pruebaimagen');
    Route::post('refresh', 'UsersController@refreshpass');
    Route::post('allusersonlypost', 'UsersController@allusersonlypost');///solo lista de tus usuarios
    Route::post('setMetodo', 'UsersController@SetMetodo');
    Route::post('interfaceuser', 'UsersController@interfaceuser');

    
});

  

    Route::group(['middleware' => 'admin'], function ($router) {

        Route::group(['prefix' => '/user'], function () {
            Route::post('updateuser', 'UsersController@updateuser');
            Route::post('addrole', 'UsersController@addrolesandpermissionsuser');
            Route::post('interfaceuseradmin', 'UsersController@interfaceuseradmin');

        

        });

              Route::group(['prefix' => '/role'], function () {
            Route::post('all', 'RolesController@getall');
            Route::post('update', 'RolesController@update');
            Route::post('create', 'RolesController@create');
            Route::post('destroy', 'RolesController@destroy');
        });

        Route::resource('mail',        'MailController');
        Route::get('prepareSend/{id}', 'MailController@prepareSend')->name('prepareSend');
        Route::post('mailSend/{id}',   'MailController@send')->name('mailSend');

        Route::resource('bread',  'BreadController');   //create BREAD (resource)

        Route::resource('users', 'UsersController')->except( ['create', 'store'] );
        Route::get('menu/edit', 'MenuEditController@index');
        Route::get('menu/edit/selected', 'MenuEditController@menuSelected');
        Route::get('menu/edit/selected/switch', 'MenuEditController@switch');

        Route::prefix('menu/menu')->group(function () { 
            Route::get('/',         'MenuEditController@index')->name('menu.menu.index');
            Route::get('/create',   'MenuEditController@create')->name('menu.menu.create');
            Route::post('/store',   'MenuEditController@store')->name('menu.menu.store');
            Route::get('/edit',     'MenuEditController@edit')->name('menu.menu.edit');
            Route::post('/update',  'MenuEditController@update')->name('menu.menu.update');
            Route::get('/delete',   'MenuEditController@delete')->name('menu.menu.delete');
        });
        Route::prefix('menu/element')->group(function () { 
            Route::get('/',             'MenuElementController@index')->name('menu.index');
            Route::get('/move-up',      'MenuElementController@moveUp')->name('menu.up');
            Route::get('/move-down',    'MenuElementController@moveDown')->name('menu.down');
            Route::get('/create',       'MenuElementController@create')->name('menu.create');
            Route::post('/store',       'MenuElementController@store')->name('menu.store');
            Route::get('/get-parents',  'MenuElementController@getParents');
            Route::get('/edit',         'MenuElementController@edit')->name('menu.edit');
            Route::post('/update',      'MenuElementController@update')->name('menu.update');
            Route::get('/show',         'MenuElementController@show')->name('menu.show');
            Route::get('/delete',       'MenuElementController@delete')->name('menu.delete');
        });
        Route::prefix('media')->group(function ($router) {
            Route::get('/',                 'MediaController@index')->name('media.folder.index');
            Route::get('/folder/store',     'MediaController@folderAdd')->name('media.folder.add');
            Route::post('/folder/update',   'MediaController@folderUpdate')->name('media.folder.update');
            Route::get('/folder',           'MediaController@folder')->name('media.folder');
            Route::post('/folder/move',     'MediaController@folderMove')->name('media.folder.move');
            Route::post('/folder/delete',   'MediaController@folderDelete')->name('media.folder.delete');;

            Route::post('/file/store',      'MediaController@fileAdd')->name('media.file.add');
            Route::get('/file',             'MediaController@file');
            Route::post('/file/delete',     'MediaController@fileDelete')->name('media.file.delete');
            Route::post('/file/update',     'MediaController@fileUpdate')->name('media.file.update');
            Route::post('/file/move',       'MediaController@fileMove')->name('media.file.move');
            Route::post('/file/cropp',      'MediaController@cropp');
            Route::get('/file/copy',        'MediaController@fileCopy')->name('media.file.copy');

            Route::get('/file/download',    'MediaController@fileDownload');
        });

        Route::resource('roles',               'RolesController');
        Route::get('/roles/move/move-up',      'RolesController@moveUp')->name('roles.up');
        Route::get('/roles/move/move-down',    'RolesController@moveDown')->name('roles.down');

    });
    Route::post('pruebas', 'AuthController@pruebas');

    Route::post('lazyTable', 'LazyTableController@index');
});

