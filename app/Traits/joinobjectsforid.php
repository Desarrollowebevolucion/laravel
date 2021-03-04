<?php
/*
*   01.02.2021
*   my traits.php
*/
namespace App\Traits;




trait joinobjectsforid{
    public function depuraobject($arrayin){
        $arraynuevo=[];

        for($c=0;$c<count($arrayin);$c++){
                      $noencontrado=true;
             for($d=0;$d<count($arraynuevo);$d++){
                 if($arraynuevo[$d]['id']==$arrayin[$c]['id']){
                    $noencontrado=false;
                break;
                 } 

                    }
          if($noencontrado){
      array_push($arraynuevo,$arrayin[$c]);
           }    

        }
                return $arraynuevo;


    }

    public function joinarrays($array){
        $nuevo=[];
        for($a=0;$a<count($array);$a++){
        $nuevo=array_merge($nuevo,$array[$a]['users']);
        }
        return $nuevo;
    }

}

