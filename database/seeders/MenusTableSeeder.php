<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class MenusTableSeeder extends Seeder
{
    private $menuId = null;
    private $dropdownId = array();
    private $dropdown = false;
    private $sequence = 1;
    private $joinData = array();
    private $translationData = array();
    private $defaultTranslation = 'en';
    private $adminRole = null;
    private $userRole = null;

    public function join($roles, $menusId){
        $roles = explode(',', $roles);
        foreach($roles as $role){
            array_push($this->joinData, array('role_name' => $role, 'menus_id' => $menusId));
        }
    }

    /*
        Function assigns menu elements to roles
        Must by use on end of this seeder
    */
    public function joinAllByTransaction(){
        DB::beginTransaction();
        foreach($this->joinData as $data){
            DB::table('menu_role')->insert([
                'role_name' => $data['role_name'],
                'menus_id' => $data['menus_id'],
            ]);
        }
        DB::commit();
    }

    public function addTranslation($lang, $name, $menuId){
        array_push($this->translationData, array(
            'name' => $name,
            'lang' => $lang,
            'menus_id' => $menuId
        ));
    }

    /*
        Function insert All translations
        Must by use on end of this seeder
    */
    public function insertAllTranslations(){
        DB::beginTransaction();
        foreach($this->translationData as $data){
            DB::table('menus_lang')->insert([
                'name' => $data['name'],
                'lang' => $data['lang'],
                'menus_id' => $data['menus_id']
            ]);
        }
        DB::commit();
    }

    public function insertLink($roles, $name, $href, $icon = null){
        if($this->dropdown === false){
            DB::table('menus')->insert([
                'slug' => 'link',
                'icon' => $icon,
                'href' => $href,
                'menu_id' => $this->menuId,
                'sequence' => $this->sequence
            ]);
        }else{
            DB::table('menus')->insert([
                'slug' => 'link',
                'icon' => $icon,
                'href' => $href,
                'menu_id' => $this->menuId,
                'parent_id' => $this->dropdownId[count($this->dropdownId) - 1],
                'sequence' => $this->sequence
            ]);
        }
        $this->sequence++;
        $lastId = DB::getPdo()->lastInsertId();
        $this->join($roles, $lastId);
        $this->addTranslation($this->defaultTranslation, $name, $lastId);
        $permission = Permission::where('name', '=', $name)->get();
        if(empty($permission)){
            $permission = Permission::create(['name' => 'visit ' . $name]);
        }
        $roles = explode(',', $roles);
        if(in_array('user', $roles)){
            $this->userRole->givePermissionTo($permission);
        }
        if(in_array('admin', $roles)){
            $this->adminRole->givePermissionTo($permission);
        }
        return $lastId;
    }

    public function insertTitle($roles, $name){
        DB::table('menus')->insert([
            'slug' => 'title',
            'menu_id' => $this->menuId,
            'sequence' => $this->sequence
        ]);
        $this->sequence++;
        $lastId = DB::getPdo()->lastInsertId();
        $this->join($roles, $lastId);
        $this->addTranslation($this->defaultTranslation, $name, $lastId);
        return $lastId;
    }

    public function beginDropdown($roles, $name, $href='', $icon=''){
        if(count($this->dropdownId)){
            $parentId = $this->dropdownId[count($this->dropdownId) - 1];
        }else{
            $parentId = null;
        }
        DB::table('menus')->insert([
            'slug' => 'dropdown',
            'icon' => $icon,
            'menu_id' => $this->menuId,
            'sequence' => $this->sequence,
            'parent_id' => $parentId,
            'href' => $href,
        ]);
        $lastId = DB::getPdo()->lastInsertId();
        array_push($this->dropdownId, $lastId);
        $this->dropdown = true;
        $this->sequence++;
        $this->join($roles, $lastId);
        $this->addTranslation($this->defaultTranslation, $name, $lastId);
        return $lastId;
    }

    public function endDropdown(){
        $this->dropdown = false;
        array_pop( $this->dropdownId );
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* Get roles */
        $this->adminRole = Role::where('name' , '=' , 'admin' )->first();
        if(empty($this->adminRole)){
            $this->adminRole = Role::create(['name' => 'admin']);
        }
        $this->userRole = Role::where('name' , '=' , 'user' )->first();
        if(empty($this->userRole)){
            $this->userRole = Role::create(['name' => 'user']);
        }
        /* Create Translation languages */
        DB::table('menu_lang_lists')->insert([
            'name' => 'English',
            'short_name' => 'en',
            'is_default' => true
        ]);
        DB::table('menu_lang_lists')->insert([
            'name' => 'Polish',
            'short_name' => 'pl'
        ]);
        
        /* Create Sidebar menu */
        DB::table('menulist')->insert([
            'name' => 'sidebar menu'
        ]);
        $this->menuId = DB::getPdo()->lastInsertId();  //set menuId
        
        $id = $this->insertLink('guest,user,admin', 'Dashboard', '/', 'cil-speedometer');
        $this->addTranslation('pl', 'Panel', $id);
        $id = $this->insertLink('guest', 'Login', '/login', 'cil-account-logout');
        $this->addTranslation('pl', 'Logowanie', $id);
        $id = $this->insertLink('guest', 'Register', '/register', 'cil-account-logout');
        $this->addTranslation('pl', 'Rejestracja', $id);

        $id = $this->beginDropdown('admin', 'Settings', '/', 'cil-puzzle');
        $id = $this->addTranslation('pl', 'Ustawienia', $id);
            $id = $this->insertLink('admin', 'Media',    '/media');
            $id = $this->addTranslation('pl', 'Media', $id);
            $id = $this->insertLink('admin', 'Users',    '/users');
            $id = $this->addTranslation('pl', 'Użytkownicy', $id);
            $id = $this->insertLink('admin', 'Menu',    '/menu');
            $id = $this->addTranslation('pl', 'Menu', $id);
            $id = $this->insertLink('admin', 'BREAD',    '/bread');
            $id = $this->addTranslation('pl', 'BREAD', $id);
            $id = $this->insertLink('admin', 'Email',    '/email');
            $id = $this->addTranslation('pl', 'Email', $id);
        $this->endDropdown();

        $id = $this->insertTitle('user,admin', 'Theme');
        $this->addTranslation('pl', 'Motyw', $id);
        $id = $this->insertLink('user,admin', 'Colors', '/colors', 'cil-drop');
        $this->addTranslation('pl', 'Kolory', $id);
        $id = $this->insertLink('user,admin', 'Typography', '/typography', 'cil-pencil');
        $this->addTranslation('pl', 'Typografia', $id);
        $id = $this->insertTitle('user,admin', 'Components');
        $this->addTranslation('pl', 'Komponenty', $id);
        $id = $this->beginDropdown('user,admin', 'Base', '/base', 'cil-puzzle');
        $this->addTranslation('pl', 'Podstawa', $id);
            $id = $this->insertLink('user,admin', 'Breadcrumb',    '/base/breadcrumb');
            $this->addTranslation('pl', 'Chlebek', $id);
            $id = $this->insertLink('user,admin', 'Cards',         '/base/cards');
            $this->addTranslation('pl', 'Karty', $id);
            $id = $this->insertLink('user,admin', 'Carousel',      '/base/carousel');
            $this->addTranslation('pl', 'Karuzela', $id);
            $id = $this->insertLink('user,admin', 'Collapse',      '/base/collapse');
            $this->addTranslation('pl', 'Zapadki', $id);
            $id = $this->insertLink('user,admin', 'Jumbotron',     '/base/jumbotron');
            $this->addTranslation('pl', 'Karta', $id);
            $id = $this->insertLink('user,admin', 'List group',    '/base/list-group');
            $this->addTranslation('pl', 'Zgrupowana lista', $id);
            $id = $this->insertLink('user,admin', 'Navs',          '/base/navs');
            $this->addTranslation('pl', 'Nawigacja', $id);
            $id = $this->insertLink('user,admin', 'Navbars',       '/base/navbars');
            $this->addTranslation('pl', 'Pasek Nawigacyjny', $id);
            $id = $this->insertLink('user,admin', 'Pagination',    '/base/pagination');
            $this->addTranslation('pl', 'Paginacja', $id);
            $id = $this->insertLink('user,admin', 'Popovers',      '/base/popovers');
            $this->addTranslation('pl', 'Podpowiedź', $id);
            $id = $this->insertLink('user,admin', 'Progress',      '/base/progress');
            $this->addTranslation('pl', 'Pasek postępu', $id);
           // $id = $this->insertLink('user,admin', 'Scrollspy',     '/base/scrollspy');  
            $id = $this->insertLink('user,admin', 'Switches',      '/base/switches');
            $this->addTranslation('pl', 'Przełączniki', $id);
            //$id = $this->insertLink('user,admin', 'Tables',        '/base/tables');
            $id = $this->insertLink('user,admin', 'Tabs',          '/base/tabs');
            $this->addTranslation('pl', 'Zakładki', $id);
            $id = $this->insertLink('user,admin', 'Tooltips',      '/base/tooltips');
            $this->addTranslation('pl', 'Wskazówka', $id);
        $this->endDropdown();
        $id = $this->beginDropdown('user,admin', 'Buttons', '/buttons', 'cil-cursor');
        $this->addTranslation('pl', 'Przyciski', $id);
            $id = $this->insertLink('user,admin', 'Buttons',           '/buttons/buttons');
            $this->addTranslation('pl', 'Przyciski', $id);
            $id = $this->insertLink('user,admin', 'Buttons Group',     '/buttons/button-group');
            $this->addTranslation('pl', 'Grupy przycisków', $id);
            $id = $this->insertLink('user,admin', 'Dropdowns',         '/buttons/dropdowns');
            $this->addTranslation('pl', 'Przyciski z rozwijanym menu', $id);
            $id = $this->insertLink('user,admin', 'Brand Buttons',     '/buttons/brand-buttons');
            $this->addTranslation('pl', 'Przyciski z logotypami', $id);
        $this->endDropdown();
        $id = $this->insertLink('user,admin', 'Charts', '/charts', 'cil-chart-pie');
        $this->addTranslation('pl', 'Wykresy', $id);
        $id = $this->beginDropdown('user,admin', 'Editors', '/editors', 'cil-code');
        $this->addTranslation('pl', 'Edytor', $id);
            $id = $this->insertLink('user,admin', 'Code editors',      '/editors/code-editors');
            $this->addTranslation('pl', 'Edytor kodu', $id);
            $id = $this->insertLink('user,admin', 'Text editors',      '/editors/text-editors');
            $this->addTranslation('pl', 'Edytor tekstu', $id);
        $this->endDropdown();
        $id = $this->beginDropdown('user,admin', 'Forms', '/forms', 'cil-notes');
        $this->addTranslation('pl', 'Formularze', $id);
            $id = $this->insertLink('user,admin', 'Basic forms',      '/forms/basic-forms');
            $this->addTranslation('pl', 'Podstawowe formularze', $id);
            $id = $this->insertLink('user,admin', 'Adcanced forms',   '/forms/advanced-forms');
            $this->addTranslation('pl', 'Zaawansowane formularze', $id);
            $id = $this->insertLink('user,admin', 'Validation forms', '/forms/validation-forms');
            $this->addTranslation('pl', 'Walidacja', $id);  
        $this->endDropdown();
        $id = $this->insertLink('user,admin', 'Google Maps', '/google-maps', 'cil-map');
        $this->addTranslation('pl', 'Mapy Google', $id);
        $id = $this->beginDropdown('user,admin', 'Icons', '/icon', 'cil-star');
        $this->addTranslation('pl', 'Ikony', $id);
            $id = $this->insertLink('user,admin', 'CoreUI Icons',      '/icon/coreui-icons');
            $this->addTranslation('pl', 'CoreUI ikony', $id);
            $id = $this->insertLink('user,admin', 'Flags',             '/icon/flags');
            $this->addTranslation('pl', 'Flagi', $id);
            $id = $this->insertLink('user,admin', 'Brands',            '/icon/brands');
            $this->addTranslation('pl', 'Logotypy', $id);
        $this->endDropdown();
        $id = $this->beginDropdown('user,admin', 'Notifications', '/notifications', 'cil-bell');
        $this->addTranslation('pl', 'Powiadomienia', $id);
            $id = $this->insertLink('user,admin', 'Alerts',     '/notifications/alerts');
            $this->addTranslation('pl', 'Alerty', $id);
            $id = $this->insertLink('user,admin', 'Badge',      '/notifications/badge');
            $this->addTranslation('pl', 'Etykieta', $id);
            $id = $this->insertLink('user,admin', 'Modals',     '/notifications/modals');
            $this->addTranslation('pl', 'Okno powiadomienia', $id);
            $id = $this->insertLink('user,admin', 'Toaster',    '/notifications/toaster');
            $this->addTranslation('pl', 'Tosty', $id);
        $this->endDropdown();
        $id = $this->beginDropdown('user,admin', 'Plugins', '/plugins', 'cil-bolt');
        $this->addTranslation('pl', 'Wtyczki', $id);
            $id = $this->insertLink('user,admin', 'Draggable',    '/plugins/draggable');
            $this->addTranslation('pl', 'Elementy przesówne', $id);
            $id = $this->insertLink('user,admin', 'Calendar',     '/plugins/calendar');
            $this->addTranslation('pl', 'Kalendarz', $id);
            $id = $this->insertLink('user,admin', 'Spinners',     '/plugins/spinners');
            $this->addTranslation('pl', 'Kręciołki', $id);
        $this->endDropdown();
        $id = $this->beginDropdown('user,admin', 'Tables', '/tables', 'cil-list');
        $this->addTranslation('pl', 'Tablice', $id);
            $id = $this->insertLink('user,admin', 'Basic Tables',                  '/tables/tables');
            $this->addTranslation('pl', 'Podstawowe tablice', $id);
            $id = $this->insertLink('user,admin', 'Advanced tables',               '/tables/advanced-tables');
            $this->addTranslation('pl', 'Zaawansowane tablice', $id);
            $id = $this->insertLink('user,admin', 'Lazy loading tables',           '/tables/lazy-loading-tables');
            $this->addTranslation('pl', 'Leniwie ładowane tablice', $id);
            $id = $this->insertLink('user,admin', 'Lazy loading tables scroll',    '/tables/lazy-loading-tables-scroll');
            $this->addTranslation('pl', 'Tablice ładowanie leniwie podczas przesówania', $id);
        $this->endDropdown();
        $id = $this->insertLink('user,admin', 'Widgets', '/widgets', 'cil-calculator');
        $this->addTranslation('pl', 'Widżety', $id);
        $id = $this->insertTitle('user,admin', 'Extras');
        $this->addTranslation('pl', 'Ekstra', $id);
        $id = $this->beginDropdown('user,admin', 'Pages', '/pages', 'cil-star');
        $this->addTranslation('pl', 'Strony', $id);
            $id = $this->insertLink('user,admin', 'Login',         '/login');
            $this->addTranslation('pl', 'Logowanie', $id);
            $id = $this->insertLink('user,admin', 'Register',      '/register');
            $this->addTranslation('pl', 'Rejestracja', $id);
            $id = $this->insertLink('user,admin', 'Error 404',     '/pages/404');
            $this->addTranslation('pl', 'Błąd 404', $id);
            $id = $this->insertLink('user,admin', 'Error 500',     '/pages/500');
            $this->addTranslation('pl', 'Błąd 500', $id);
        $this->endDropdown();
        $id = $this->beginDropdown('user,admin', 'Apps', '/apps', 'cil-layers');
        $this->addTranslation('pl', 'Aplikacje', $id);
            $id = $this->beginDropdown('user,admin', 'Invoicing', '/apps/invoicing', 'cil-description');
            $this->addTranslation('pl', 'Faktury', $id);
                $id = $this->insertLink('user,admin', 'Invoice',     '/apps/invoicing/invoice');
                $this->addTranslation('pl', 'Faktura', $id);
            $this->endDropdown();
            $id = $this->beginDropdown('user,admin', 'Email', '/apps/email', 'cil-envelope-open');
            $this->addTranslation('pl', 'E-mail', $id);
                $id = $this->insertLink('user,admin', 'Inbox',       '/apps/email/inbox');
                $this->addTranslation('pl', 'Skrzynka odbiorcza', $id);
                $id = $this->insertLink('user,admin', 'Message',     '/apps/email/message');
                $this->addTranslation('pl', 'Wiadomość', $id);
                $id = $this->insertLink('user,admin', 'Compose',     '/apps/email/compose');
                $this->addTranslation('pl', 'Nowa wiadomość', $id);
            $this->endDropdown();
        $this->endDropdown();
        $id = $this->insertLink('guest,user,admin', 'Download CoreUI', 'https://coreui.io', 'cil-cloud-download');
        $this->addTranslation('pl', 'Pobierz CoreUI', $id);
        $id = $this->insertLink('guest,user,admin', 'Try CoreUI PRO', 'https://coreui.io/pro/', 'cil-layers');
        $this->addTranslation('pl', 'Wypróbuj CoreUI PRO', $id);

        /* Create top menu */
        DB::table('menulist')->insert([
            'name' => 'top_menu'
        ]);
        $this->menuId = DB::getPdo()->lastInsertId();  //set menuId
        $id = $this->beginDropdown('guest,user,admin', 'Pages');
        $this->addTranslation('pl', 'Strony', $id);
            $id = $this->insertLink('guest,user,admin', 'Dashboard',    '/');
            $this->addTranslation('pl', 'Panel', $id);
            $id = $this->insertLink('user,admin', 'Notes',              '/notes');
            $this->addTranslation('pl', 'Notatki', $id);
            $id = $this->insertLink('admin', 'Users',                   '/users');
            $this->addTranslation('pl', 'Urzytkownicy', $id);
        $this->endDropdown();
        $id = $this->beginDropdown('admin', 'Settings');
        $this->addTranslation('pl', 'Ustawienia', $id);
            $id = $this->insertLink('admin', 'Edit menu',               '/menu');
            $this->addTranslation('pl', 'Edytuj Menu', $id);
            $id = $this->insertLink('admin', 'Edit roles',              '/roles');
            $this->addTranslation('pl', 'Edytuj role', $id);
            $id = $this->insertLink('admin', 'Media',                   '/media');
            $this->addTranslation('pl', 'Media', $id);
            $id = $this->insertLink('admin', 'BREAD',                   '/bread');
            $this->addTranslation('pl', 'BREAD', $id);
            $id = $this->insertLink('admin', 'E-mail',                  '/email');
            $this->addTranslation('pl', 'E-mail', $id);
        $this->endDropdown();



        $this->joinAllByTransaction();   ///   <===== Must by use on end of this seeder
        $this->insertAllTranslations();  ///   <===== Must by use on end of this seeder
    }
}
