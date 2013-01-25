<?php
 class ListGoodsAction extends CommonAction{
     public function index(){
         $goods_data = M('goods');
         $goods = $goods_data->select();
         $this->assign("goods",$goods);
         $this->display();
     }
     /**
      * 
      * 将商品的规格分配到模版中
      */
     private function gs(){
         $gid = $_GET['id'];
         $goods = M('goods');         
         $goods_mes = $goods->where(array('id'=>$gid))->select();
         $this->assign("goods_mes", $goods_mes);
         
         
         $good_attr = M('goods_attr');
         $attr = $good_attr->where(array('gid'=>$gid))->group('aid')->field('aid')->select();
        
         $type = M('type_attr');
         $type_all = $type->select();  
         $gt_all =array();
         foreach ($type_all as $value) {
             foreach ($attr as $v) {
                 if($v['aid']==$value['id'] && $value['type']!=0 ){
                     $value['gid']=$gid;
                     $value['value']=  explode('|', $value['value']);
                     $gt_all[]=$value;
                 }
             }
             
         }
         $this->assign('gid',$gid);
         $this->assign('attr_all',$gt_all);
     }
     
     /**
      * 显示商品规格列表
      */
     public function good_show(){
         $this->gs();
         $this->display();
     }
     /**
      * 插入库存数据
      */
     public function put_number(){
         $data =$_POST;
           
         /**
          * $key为商品的id
          * $value为id的属性库存集和
          */
         foreach ($data as $key => $value) {
             $gid = $key;
             $arr_all = $this->arr($value,$gid);           
               foreach ($arr_all as $value) {
             $db = M('goods_list');             
             $res = $db->data($value)->add();         
             if(!$res){
                 $this->error('操作失败',U('index'));
             }
         }
         $this->success('操作成功',U('index'));         
           
         }
     }
     
     /**
      * 格式化成可插入数据表的数据
      * @param type $arr
      * @param type $gid
      * @return type  $arr
      */
     private function arr ($arr,$gid){        
         $arr1 = array();
         foreach ($arr as $key => $value) {
             if(is_numeric($key)){
                 $arr1[]=$value;
             }
         }
        
         foreach ($arr1 as $k2 => $v2) {
             $num = count($v2);
             break;
         }
         $arr_all=array();
         $arre =array();
         for($i=0;$i<$num;$i++){
              $str = '';
             foreach ($arr1 as $k => $v) {
                 $str .= $k.','.$v[$i].'|';
             }
             $arre['inventory'] = $arr['number'][$i];
             $arre['number']=$arr['num'][$i];
             $arre['series']=$arr['series'][$i];
             $arre['attr']= $str;
             $arre['attr']=  rtrim($arre['attr'],'|');
             $arre['gid']=$gid;
             $arr_all[]=$arre;
         }
       return $arr_all;
     }
     
     
     
     public function good_attr_edit_show(){
         $this->gs();
         $db = M ('goods_list');
         $gid = $_REQUEST['id'];
         $data = $db->where('gid = '.$gid)->select();         
         foreach ($data as $key=>$value) {
             $data[$key]['attr'] = explode("|", $value['attr']);
             foreach ($data[$key]['attr'] as $k=> $v) {
                 $data[$key]['attr'][$k]=  explode(',', $v);
             }
         }
         $this->assign('data',$data);
         $this->display();
     }
     /**
      * 插入更改的数据
      */
     
     public function attr_modify(){
         foreach ($_POST as $gid => $arrs) {
             $db = M('goods_list');
             $data = $db->where(array("gid"=>$gid))->select();
             if(count($data)){
                 $res = $db->where(array("gid"=>$gid))->delete();
                 if($res){                     
                     $arr = $this->arr($arrs,$gid);
                     $this->put_data_in($arr,$gid);
                 }else{
                     $this->error('操作失败',U("good_attr_edit_show?id=$gid"));
                 }
             }else{
                  $arr = $this->arr($arrs,$gid);
                  $this->put_data_in($arr,$gid);
             }
         }
     }
     
     private function put_data_in($arr,$gid){
         $db = M('goods_list');
          foreach ($arr as $mes) {
                         $ress = $db->data($mes)->add();
                         if($ress){
                             $this->error('操作成功',U("good_attr_edit_show?id=$gid"));
                         }else{
                             $this->error('操作失败',U("good_attr_edit_show?id=$gid"));
                         }
                     }
     }
     
     
     
     
     
     
     
 }
?>