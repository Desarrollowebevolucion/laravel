<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Notes;

use App\Repositories\LazyTable\LazyTableRepository;

class LazyLoadingTableTest extends TestCase
{
    use DatabaseMigrations;

    public function sorting($array){
        for($i=count($array);$i>=0;$i--){
            for($j=0;$j<$i-1;$j++){
                if( $array[$j]->title > $array[$j+1]->title ){ 
                $tmp=$array[$j];
                $array[$j]=$array[$j+1];
                $array[$j+1]=$tmp;
                }
            }
        }
        return $array;
    }

    /**
     * @return void
     */
    public function testLazyTableRepository()
    {
        /* create notes array */
        $ltr = new LazyTableRepository();
        $notes = array();
        for($i = 0; $i < 15; $i++){
            array_push($notes, Notes::factory()->create());
        }
        /* case: simple */
        $sorter         = array('column'=>'', 'asc'=>false);
        $tableFilter    = '';
        $columnFilter   = array();
        $itemsLimit     = 3;
        $result = json_encode($ltr->get( $sorter, $tableFilter, $columnFilter, $itemsLimit ));
        $this->assertStringContainsString($notes[0]->title, $result);
        $this->assertStringContainsString($notes[1]->title, $result);
        $this->assertStringContainsString($notes[2]->title, $result);
        $this->assertStringNotContainsString($notes[3]->title, $result);
        $this->assertStringNotContainsString($notes[4]->title, $result);
        /* case: column filter */
        $sorter         = array('column'=>'', 'asc'=>false);
        $tableFilter    = '';
        $columnFilter   = array('title' => $notes[9]->title);
        $itemsLimit     = 3;
        $result = json_encode($ltr->get( $sorter, $tableFilter, $columnFilter, $itemsLimit ));
        $this->assertStringContainsString($notes[9]->title, $result);
        $this->assertStringNotContainsString($notes[0]->title, $result);
        $this->assertStringNotContainsString($notes[1]->title, $result);
        $this->assertStringNotContainsString($notes[2]->title, $result);
        $this->assertStringNotContainsString($notes[8]->title, $result);
        $this->assertStringNotContainsString($notes[10]->title, $result);
        /* case: table filter */
        $sorter         = array('column'=>'', 'asc'=>false);
        $tableFilter    = $notes[9]->title;
        $columnFilter   = array();
        $itemsLimit     = 3;
        $result = json_encode($ltr->get( $sorter, $tableFilter, $columnFilter, $itemsLimit ));
        $this->assertStringContainsString($notes[9]->title, $result);
        $this->assertStringNotContainsString($notes[0]->title, $result);
        $this->assertStringNotContainsString($notes[1]->title, $result);
        $this->assertStringNotContainsString($notes[2]->title, $result);
        $this->assertStringNotContainsString($notes[8]->title, $result);
        $this->assertStringNotContainsString($notes[10]->title, $result);
        /* sort array */
        $notes = $this->sorting( $notes );
        /* case: sorter asc */
        $sorter         = array('column'=>'title', 'asc'=>true);
        $tableFilter    = '';
        $columnFilter   = array();
        $itemsLimit     = 3;
        $result = json_encode($ltr->get( $sorter, $tableFilter, $columnFilter, $itemsLimit ));
        $this->assertStringContainsString($notes[0]->title, $result);
        $this->assertStringContainsString($notes[1]->title, $result);
        $this->assertStringContainsString($notes[2]->title, $result);
        /* case: sorter desc */
        $sorter         = array('column'=>'title', 'asc'=>false);
        $tableFilter    = '';
        $columnFilter   = array();
        $itemsLimit     = 3;
        $result = json_encode($ltr->get( $sorter, $tableFilter, $columnFilter, $itemsLimit ));
        $this->assertStringContainsString($notes[14]->title, $result);
        $this->assertStringContainsString($notes[13]->title, $result);
        $this->assertStringContainsString($notes[12]->title, $result);
    }

    /**
     * @return void
     */
    public function testCanGetDataToLazyTableController()
    {
        $notes = array();
        for($i = 0; $i < 15; $i++){
            array_push($notes, Notes::factory()->create());
        }
        $response = $this->post('/api/lazyTable', array(
            'sorter'       => array('column'=>'', 'asc' => false),
            'tableFilter'  => '',
            'columnFilter' => array(),
            'itemsLimit'   => 3
        ));
        $response->assertStatus(200)->assertJson(
            [
                'last_page' => 5,
                'per_page' => 3,
                'total' => 15,
                'data' => [
                    [
                        'title' => $notes[0]->title
                    ],
                    [
                        'title' => $notes[1]->title
                    ],
                    [
                        'title' => $notes[2]->title
                    ],
                ]
            ]
        );
        $response = $this->post('/api/lazyTable?page=2', array(
            'sorter'       => array('column'=>'', 'asc' => false),
            'tableFilter'  => '',
            'columnFilter' => array(),
            'itemsLimit'   => 3
        ));
        $response->assertStatus(200)->assertJson(
            [
                'last_page' => 5,
                'per_page' => 3,
                'total' => 15,
                'data' => [
                    [
                        'title' => $notes[3]->title
                    ],
                    [
                        'title' => $notes[4]->title
                    ],
                    [
                        'title' => $notes[5]->title
                    ],
                ]
            ]
        );
    }

    /**
     * @return void
     */
    public function testCanGetFiltredDataToLazyTableController()
    {
        $notes = array();
        for($i = 0; $i < 15; $i++){
            array_push($notes, Notes::factory()->create());
        }
        $response = $this->post('/api/lazyTable', array(
            'sorter'       => array('column'=>'', 'asc' => false),
            'tableFilter'  => '',
            'columnFilter' => array('title' => $notes[7]->title),
            'itemsLimit'   => 3
        ));
        $response->assertStatus(200)->assertJson(
            [
                'last_page' => 1,
                'per_page' => 3,
                'total' => 1,
                'data' => [
                    [
                        'title' => $notes[7]->title
                    ],
                ]
            ]
        );
        $response = $this->post('/api/lazyTable', array(
            'sorter'       => array('column'=>'', 'asc' => false),
            'tableFilter'  => $notes[9]->content,
            'columnFilter' => array(),
            'itemsLimit'   => 3
        ));
        $response->assertStatus(200)->assertJson(
            [
                'last_page' => 1,
                'per_page' => 3,
                'total' => 1,
                'data' => [
                    [
                        'title' => $notes[9]->title
                    ],
                ]
            ]
        );
    }
}
