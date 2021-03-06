<?php

namespace app\controllers;

use Yii;
use app\models\Album;
use app\models\AlbumSearch;
use app\models\Photo;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UnauthorizedHttpException;

/**
 * AlbumController implements the CRUD actions for Album model.
 */
class AlbumController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['view', 'home'],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['view', 'home', 'edit', 'reorder', 'delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Displays a single Album model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id=0, $url_text=null)
    {
        $model = $this->findModel($id, $url_text);

        $is_mobile = \Yii::$app->devicedetect->isMobile() && !\Yii::$app->devicedetect->isTablet();;

        $this->layout = $is_mobile ? 'mobile' : 'albums';
        $view = $is_mobile ? 'mobile' : 'view';
        return $this->render($view, [
            'model' => $model,
        ]);
    }

    public function actionHome()
    {
        return $this->actionView(5);
    }

    /**
     * Updates an existing Album model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionEdit($id=0, $url_text=null)
    {
        $model = $this->findModel($id, $url_text);
        if (!\Yii::$app->user->can('modifyAlbum', ['content' => $model])) {
            throw new UnauthorizedHttpException("You don't have permission to modify this album."); 
        }

        $this->layout = 'editor';

        if (Yii::$app->request->isPost) {
           $model->file = UploadedFile::getInstances($model, 'file');
           
           if ($model->file /*&& $model->validate()*/) { //TODO
               $this->handleFileUploads($model->file, $model->id);
           }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['edit', 'id' => $model->id]);
        } else {
            return $this->render('edit', [
                'model' => $model,
            ]);
        }
    }

    public function actionReorder($id)
    {
        $success = isset($_POST['order']);
        if($success)
	{
	    $order = $_POST['order'];
            foreach ($order as $position=>$id){
                $model = Photo::findOne($id);
                if (!\Yii::$app->user->can('modifyAlbum', ['content' => $model])) {
                    throw new UnauthorizedHttpException("You don't have permission to modify this album."); 
                }
                $model->position = $position;
                $success &= $model->save();
            }
        }
        return \yii\helpers\Json::encode(array("success"=>$success));
    }

    /**
     * Deletes an existing Album model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (!\Yii::$app->user->can('modifyAlbum', ['content' => $model])) {
            throw new UnauthorizedHttpException("You don't have permission to delete this album."); 
        }
        $model->delete();

        return $this->redirect(['index']);
    }

   protected function getMaxPosition($album_id)
   {
      $max = (new \yii\db\Query())
           ->select("MAX(position)")->from("photo")
           ->where('album_id=:cond1', array(':cond1'=>$album_id))
           ->andWhere('user_id=:cond2', array(':cond2'=>Yii::$app->user->getId()))
           ->scalar();
      return $max;
   }
    
    protected function handleFileUploads($files, $album_id)
    {
        $position = $this->getMaxPosition($album_id);
        foreach ($files as $file) {
            $position++;
            $this->handleFileUpload($file, $album_id, $position);
        }
    }
    
   protected function handleFileUpload($file, $album_id, $position)
   {
      if ($file){
         $photo = new Photo();
         $photo->user_id = Yii::$app->user->getId();
         $photo->album_id = $album_id;
         $photo->position = $position;

         $photo->filename = Yii::$app->utility->generateFilename("jpg");
         $photo->content_type = $file->type;

         $size = getimagesize($file->tempName);
         $photo->width = $size[0];
         $photo->height = $size[1];
         $photo->aspect_ratio = $size[0]/$size[1];

         if($photo->save()){
            $file->saveAs($photo->getServerPath('original'));
            $this->processUploadedImage($photo->filename);
         }
      }
   }

   protected function saveUploadedImage($filename, $target, $q, $w=0)
   {
      $image = \Yii::$app->image->load(\Yii::$app->utility->getPhotoPath('original', $filename));
      if ($w > 0){
         $image->resize($w, 0);
      }
      $image->save(\Yii::$app->utility->getPhotoPath($target, $filename), $q);
   }

   protected function processUploadedImage($filename)
   {
      $this->saveUploadedImage($filename, 'full', 92);
      $this->saveUploadedImage($filename, '1600', 92, 1600);
      $this->saveUploadedImage($filename, '800', 92, 800);
      $this->saveUploadedImage($filename, '400', 92, 400);
   }

    /**
     * Finds the Album model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Album the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $url_text=null)
    {
        $model = ($url_text === null) ? 
            Album::findOne($id) : Album::findOne(['url_text' => $url_text]);

        if ($model === null)
            throw new NotFoundHttpException('The requested page does not exist.');

        return $model;
    }
}
