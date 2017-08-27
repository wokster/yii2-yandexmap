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

class YandexGetCoordsWidget extends Widget
{
  public $model;
  public $attribute = 'coords';
  public $center = '55.753994, 37.622093';
  public $autoinit = true;
  public function init(){
    return parent::init();
  }
  public function run(){
    if($this->model){
      $input = Html::activeInput('text',$this->model,$this->attribute,['class'=>'form-control']);
      if(!empty($this->model[$this->attribute]))
        $this->center = $this->model[$this->attribute];
    }else{
      $input = Html::input('text',$this->attribute,'',['class'=>'form-control']);
    }
    $this->view->registerJsFile('https://api-maps.yandex.ru/2.1/?lang=en',['position'=>1,'type'=>'text/javascript']);
    if($this->autoinit){
      $this->view->registerJs("ymaps.ready(init);");
    }
    $this->view->registerJs("
function init() {
    var myPlacemark,
        myMap = new ymaps.Map('map', {
            center: [".$this->center."],
            zoom: 15
        }, {
            searchControlProvider: 'yandex#search'
        });
    myPlacemark = createPlacemark([".$this->center."]);
    myMap.geoObjects.add(myPlacemark);
    // Слушаем клик на карте
    myMap.events.add('click', function (e) {
        var coords = e.get('coords');
        $('#".Html::getInputId($this->model,$this->attribute)."').val(coords);
        // Если метка уже создана – просто передвигаем ее
        if (myPlacemark) {
            myPlacemark.geometry.setCoordinates(coords);
        }
        // Если нет – создаем.
        else {
            myPlacemark = createPlacemark(coords);
            myMap.geoObjects.add(myPlacemark);
            // Слушаем событие окончания перетаскивания на метке.
            myPlacemark.events.add('dragend', function () {
                getAddress(myPlacemark.geometry.getCoordinates());
            });
        }
        getAddress(coords);
    });
    myPlacemark.events.add('dragend', function (e) {
        var coords = myPlacemark.geometry.getCoordinates();
        $('#".Html::getInputId($this->model,$this->attribute)."').val(coords);
        });

    // Создание метки
    function createPlacemark(coords) {
        return new ymaps.Placemark(coords, {
            iconContent: ''
        }, {
            preset: 'islands#violetStretchyIcon',
            draggable: true
        });
    }

    // Определяем адрес по координатам (обратное геокодирование)
    function getAddress(coords) {
        myPlacemark.properties.set('iconContent', 'поиск...');
        ymaps.geocode(coords).then(function (res) {
            var firstGeoObject = res.geoObjects.get(0);
            myPlacemark.properties
                .set({
                    iconContent: firstGeoObject.properties.get('name'),
                    balloonContent: firstGeoObject.properties.get('text')
                });
        });
    }
}
    ");
    return '<div class="row">
    <div class="col-xs-5 col-sm-4 col-md-3">
    <label class="control-label">The coordinates of the place</label>
    '.$input.'
    <div class="help-block">Click in any place of the map to get coordinates.</div>
    </div>
    <div class="col-xs-7 col-sm-8 col-md-9">
      <div style="width: 100%; height: 400px;">
        <div id="map" style="width: 100%; height: 400px;"></div>
      </div>
    </div>
  </div>';
  }
}
