<?php
/**
 * Created by internetsite.com.ua
 * User: Tymofeiev Maksym
 * Date: 24.05.2016
 * Time: 12:56
 */

namespace wokster\yandexmap;


use yii\base\Widget;
use yii\bootstrap\Html;
use yii\helpers\Json;

class YandexDisplayMapWidget extends Widget
{
  public $json;
  public $center = '55.753994, 37.622093';
  public $zoom = 15;
  public $js_auto_init = true;

  public function init(){
    return parent::init();
  }
  public function run(){
    if(empty($this->json)) { // hint: https://tech.yandex.ru/maps/doc/jsapi/2.1/ref/reference/ObjectManager-docpage/#add
      $this->json = '
          {
              type: \'Feature\',
              id: 1,
              geometry: {
                  type: \'Point\',
                  coordinates: [' . $this->center . ']
              }
          }
      ';
    }
    $this->view->registerJsFile('https://api-maps.yandex.ru/2.1/?lang=ru',['position'=>1,'type'=>'text/javascript'],'yandex_map');
    $init = ($this->js_auto_init)?"ymaps.ready(init$this->id)":"";
    $this->view->registerJs($init."
var myMap".$this->id.";
function init$this->id () {
    myMap".$this->id." = new ymaps.Map('".$this->id."', {
            center: [".$this->center."],
            zoom: ".$this->zoom."
        }, {
            searchControlProvider: 'yandex#search'
        }),
        objectManager = new ymaps.ObjectManager({
            // Чтобы метки начали кластеризоваться, выставляем опцию.
            clusterize: true,
            // ObjectManager принимает те же опции, что и кластеризатор.
            gridSize: 32
        });
    myMap".$this->id.".behaviors.disable('scrollZoom');
    // Чтобы задать опции одиночным объектам и кластерам,
    // обратимся к дочерним коллекциям ObjectManager.
    objectManager.objects.options.set('preset', 'islands#greenDotIcon');
    objectManager.clusters.options.set('preset', 'islands#greenClusterIcons');
    myMap".$this->id.".geoObjects.add(objectManager);
    objectManager.add(".$this->json.");
}
    ");
    return '<div id="'.$this->id.'" style="width: 100%; min-height: 400px"></div>';
  }
}
