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

  public function init(){
    return parent::init();
  }
  public function run(){
    $this->view->registerJsFile('https://api-maps.yandex.ru/2.1/?lang=ru',['position'=>1,'type'=>'text/javascript']);
    $this->view->registerJs("
ymaps.ready(init);
var myMap;
function init () {
    myMap = new ymaps.Map('map', {
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
    myMap.behaviors.disable('scrollZoom');
    // Чтобы задать опции одиночным объектам и кластерам,
    // обратимся к дочерним коллекциям ObjectManager.
    objectManager.objects.options.set('preset', 'islands#greenDotIcon');
    objectManager.clusters.options.set('preset', 'islands#greenClusterIcons');
    myMap.geoObjects.add(objectManager);
    objectManager.add(".$this->json.");
}
    ");
    return '<div id="map" style="width: 100%; min-height: 400px"></div>';
  }
}
