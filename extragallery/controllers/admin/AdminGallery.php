<?php

require_once(_PS_MODULE_DIR_) . 'extragallery/classes/productimage.class.php';

class AdminGalleryController extends ModuleAdminController {
    public function __construct()
    {
        $this->table = 'extra_gallery';
        $this->className = 'ProductImage';
        $this->identifier = ProductImage::$definition['primary'];
        $this->bootstrap = true;
        $this->fields_list = [
            'id' => [
                'title' => 'Reference id',
                'align' => 'left'
            ],
            'product_id' => [
                'title' => 'Product id',
                'align' => 'left'
            ],
            'image' => [
                'title' => 'Image url',
                'align' => 'left'
            ]
        ];
        $this->addRowAction('delete');
        parent::__construct();
    }  
    
    public function renderForm() {
        $this->fields_form = [
            'legend' => [
                'title' => "New gallery",
                'icon' => 'icon-image'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => 'Product ID',
                    'name' => 'product_id',
                    'class' => 'input fixed-with-sm',
                    'required' => true,
                    'empty_message' => 'Please add the product ID'
                ],
                [
                    'type' => 'file',
                    'label' => 'Attach images',
                    'name' => 'image',
                    'class' => 'custom-file-input',
                    'required' => true,
                    'empty_message' => 'Please add some images',
                    'multiple' => true
                ]
            ],
            'submit' => [
                'title' => 'Add gallery'
            ]
        ];

        return parent::renderForm();
    }

    public function renderView() {
        $tplFile = dirname(__FILE__) . "/../../views/templates/admin/view.tpl";
        $tpl = $this->context->smarty->createTemplate($tplFile);
        $sql = new DbQuery();
        $sql->select('*')->from($this->table)->where('id = '. Tools::getValue('id'));
        $data = Db::getInstance()->executeS($sql);
        print_r($data);
        $tpl->assign([
            'data' => $data[0]
        ]);
        return $tpl->fetch();
    }

    public function deleteLine(){
        $id = Tools::getValue('id');
        $sql = new DbQuery();
        $sql->select('*')->from($this->table)->where('id = '. Tools::getValue('id'));
        $data = Db::getInstance()->executeS($sql);
        $image = $data[0]['image'];
        unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR .'uploads'.DIRECTORY_SEPARATOR.$image);
        if (Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'extra_gallery WHERE id = '.$id)){
            $this->confirmations[] = 'Image deleted';
        } else {
            $this->errors[]='Error! Image was not deleted';
        }

	}

    public function postProcess() {
        
        if (Tools::isSubmit('deleteextra_gallery')) {
            $this->deleteLine();
        }
        
        if(isset($_FILES["image"])) {
            for ($i = 0; $i < count($_FILES["image"]['name']); $i++) {
            $target_dir = dirname(__FILE__) . "/../../uploads/";
            $target_file = $target_dir . basename($_FILES["image"]["name"][$i]);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
            
            // Checking if image file is an actual image
            $check = getimagesize($_FILES["image"]["tmp_name"][$i]);
            if($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
            } else {
            $this->errors[] = "File is not an image.";
            $uploadOk = 0;
            }
            
            if (file_exists($target_file)) {
              $this->errors[] = "Sorry, file already exists.";
              $uploadOk = 0;
            }
            
            // Allow certain image formats
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" ) {
                $this->errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
              $uploadOk = 0;
            }
            
            if ($uploadOk == 0) {
                $this->errors[] =  "Your file was not uploaded.";

            // if everything is ok, try to upload images
            } else {
                $ext = substr($_FILES['image']['name'][$i], strrpos($_FILES['image']['name'][$i], '.') + 1);
                $file_name = md5($_FILES['image']['name'][$i]).'.'.$ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'][$i], dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$file_name)) {
                    $this->confirmations[] = "The file ". htmlspecialchars( basename( $_FILES["image"]["name"][$i])). " has been uploaded.";
                    //add product id and image file to database
                    $sql = new DbQuery();
                    Db::getInstance()->insert($this->table, array(
                        'product_id' => Tools::getValue('product_id'),
                        'image'      => $file_name,
                    ));
                } else {
                $this->errors[] =  "Sorry, there was an error uploading your file.";
              }
            }
            }
            
        }
        
    }
}