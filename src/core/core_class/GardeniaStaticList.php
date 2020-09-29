<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace gardenia_admin\src\core\core_class;



use think\Exception;

class GardeniaStaticList
{
    //成功码
    const CODE_SUCCESS = 10000;
    //错误码
    const CODE_ERROR = 10001;

    protected $listHead = [];
    protected $operBtnGroup = [];
    protected $data = [];
    protected $operateFieldList = [];
    protected $primaryKey = 'id';

    /**
     * 单例模式。由于一般情况下仅需要一个实例即可完成一般需求，多次创建实例的情况在构造数据列表方面不多见，故决定暂时废弃
     * @return GardeniaList
     */
//    public static function getInstance() {
//        if (!self::$gardeniaList) {
//            self::$gardeniaList = new GardeniaList();
//        }
//        return self::$gardeniaList;
//    }
    public function __construct($primaryKey = 'id')
    {
        $this->primaryKey = $primaryKey;
    }

    public function addListHead($field,$title) {
        $this->listHead[] = ['field'=> $field, 'title'=> $title];
        return $this;
    }

    public function addOperateButtonGroup($field,$operateButtonGroup = array(['btn_type' => 'edit','title' => '编辑'],['btn_type' => 'delete','title' => '删除'],['btn_type' => 'resume','title' => '']),
          $statusField = 'status',$statusList = ['禁用' => '禁用', '正常' => '启用']) {
        //将该字段标记为操作按钮组字段，以与表记录字段区分便于渲染
        $this->operateFieldList[] = $field;

        !$statusField && $statusField = 'status';
        !$statusList && $statusList = ['禁用' => '禁用', '正常' => '启用'];

        $supportList = ['edit','delete','resume','read'];
        //检验输入的操作按钮类型是否合法
        foreach ($operateButtonGroup as $item){
            if (!isset($item['btn_type'])){
                throw new Exception('按钮组的按钮类型字段定义错误，必须为btn_type',self::CODE_ERROR);
            }
            if (!in_array($item['btn_type'],$supportList)) {
                throw new Exception('系统默认操作按钮不支持该类型。',self::CODE_ERROR);
            }
        }

        $this->operBtnGroup[$field]['btn_group'] = $operateButtonGroup;
        $statusField && ($this->operBtnGroup[$field]['status_info']['status_field'] = $statusField);
        $statusList && ($this->operBtnGroup[$field]['status_info']['status_list'] = $statusList);

        return $this;
    }
    public function setData($data) {
        $this->data = $data;
        return $this;
    }
    public function display() {
        if (!$this->data){
            $this->data = [];
        }
        foreach ($this->data as &$item) {
            $item = array_merge($item,$this->operBtnGroup);
        }

        echo view(dirname(__FILE__).'/../../tpl/index.html',[
            'display_type'=> 'list',
            'data'=> $this->data,
            'listHead'=> $this->listHead,
            'primaryKey' => $this->primaryKey,
            'operateFieldList' => $this->operateFieldList,
        ])->getContent();
    }
}