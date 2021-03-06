<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model app\models\Album */

$this->title = $model->title;

$layouts = [
    ['value' => 'carousel', 'title' => 'Carousel'],
    ['value' => 'grid', 'title' => 'Thumbnail'],
    ['value' => 'tile', 'title' => 'Tiles'],
    ['value' => 'list', 'title' => 'Vertical'],
    ['value' => 'sidescroll', 'title' => 'Horizontal'],
]
?>
<div class="album-edit">

    <h1><?= Html::a($this->title, ['album/view', 'id'=>$model->id]) ?></h1>

    <fieldset id="layout-controls" class="gallery_layout layout page" data-edit-url="<?=Yii::$app->urlManager->createUrl(['album/edit', 'id' => $model->id])?>">
	<legend>Layout</legend>
	<?php foreach($layouts as $layout) { 
        $selected = ($model->type == $layout['value']) ? ' selected' : ''?>
	<label class="layout <?=$layout['value'].$selected?>" title="<?=$layout['title']?>">
		<input type="radio" name="layout" value="<?=$layout['value']?>">
		<em><?=$layout['title']?></em>
	</label>
        <?php } ?>
    </fieldset>

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
    <?= $form->field($model, 'file[]')->label('Add photos')->fileInput(['multiple' => true]) ?>
    <?php ActiveForm::end(); ?>
    
    <?php
    echo '<ul class="grid" id="sortable" data-reorder-url="'.Yii::$app->urlManager->createUrl(['album/reorder', 'id'=>$model->id]).'" data-edit-url="'.Yii::$app->urlManager->createUrl(['photo/update']).'" data-delete-url="'.Yii::$app->urlManager->createUrl('photo/delete').'">';
    foreach($model->photos as $photo) {
        $options = array("id" => $photo->id);
        $hidden = $photo->hide_on_pc || $photo->hide_on_mobile;
        if ($hidden) {
            $options['class'] = "hidden";
        }
        $img = Html::img($photo->getUrl(400), ['data-title' => $photo->title, 'data-description' => $photo->description]);
        $content = '<div class="thumb-container image"><div class="thumb">'.$img.'<a href="#" class="action remove"></a><a href="#" class="action hide"></a><a href="#" class="action details"></a><a href="#" class="action sort"></a></div>';
        echo Html::tag('li', $content, $options);
    } 
    echo '</ul>';
    ?> 

</div>
<div id="dialog-form" title="Edit Details" style="display:none">
  <form>
    <fieldset>
      <label for="title">Title</label>
      <input type="text" name="title" id="title" class="text ui-widget-content ui-corner-all">
      <label for="email">Description</label>
      <textarea name="description" id="description" class="text ui-widget-content ui-corner-all"></textarea>
     </fieldset>
  </form>
</div>
