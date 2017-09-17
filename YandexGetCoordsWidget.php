<?php
/**
 * Created by internetsite.com.ua
 * User: Tymofeiev Maksym
 * Date: 24.05.2016
 * Time: 12:56
 */

namespace wokster\yandexmap;


use yii\widgets\InputWidget;
use yii\bootstrap\Html;
use yii\base\Model;

class YandexGetCoordsWidget extends InputWidget
{
  public $model;
  public $attribute = 'coords';
  public $coords_attribute = 'coords';
  public $zoom_attribute = 'map_zoom';
  public $center = '55.753994, 37.622093';
  public $autoinit = true;
  public function init(){
    return parent::init();
  }
  public function run(){
    if($this->hasModel()){
      $input = Html::activeInput('text',$this->model,$this->attribute,['class'=>'form-control']);
      if($this->model->hasAttribute($this->zoom_attribute))
        $zoom_input = Html::activeInput('text',$this->model,$this->zoom_attribute,['class'=>'form-control']);
      if(!empty($this->model[$this->attribute]))
        $this->center = $this->model[$this->attribute];
    }else{
      $input = Html::input('text',$this->attribute,'',['class'=>'form-control']);
      $zoom_input = Html::input('text',$this->zoom_attribute,'',['class'=>'form-control']);
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
    myMap.events.add('boundschange', function (e) {
        var zoom = e.get('newZoom');
        $('#".Html::getInputId($this->model,$this->zoom_attribute)."').val(zoom);
    });
    // Слушаем клик на карте
    myMap.events.add('click', function (e) {
        var coords = e.get('coords');
        $('#".Html::getInputId($this->model,$this->attribute)."').val(coords);
        var zoom = myPlacemark.geometry._lastZoom;
        $('#".Html::getInputId($this->model,$this->zoom_attribute)."').val(zoom);
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
        var zoom = myPlacemark.geometry._lastZoom;
        $('#".Html::getInputId($this->model,$this->coords_attribute)."').val(coords);
        $('#".Html::getInputId($this->model,$this->zoom_attribute)."').val(zoom);
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
        <label class="control-label">Zoom for map</label>
    '.$zoom_input.'
    <div class="help-block"></div>
    </div>
    <div class="col-xs-7 col-sm-8 col-md-9">
      <div style="width: 100%; height: 400px;">
        <div id="map" style="width: 100%; height: 400px;"></div>
      </div>
    </div>
  </div>';
  }

  /**
   * @return bool whether this widget is associated with a data model.
   */
  protected function hasModel()
  {
    return $this->model instanceof Model && $this->attribute !== null;
  }
}
