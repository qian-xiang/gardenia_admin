<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace gardenia_admin\src\core\core_class;

use gardenia_admin\src\core\core_class\GardeniaConstant;
use think\Exception;
use think\facade\Db;

/**
 * Class GardeniaList 构造Layui数据表格类
 * @package gardenia_admin\src\core\core_class
 */
class GardeniaList
{

    //成功的状态码
    const CODE_SUCCESS = 10000;
    //失败的状态码
    const CODE_FAIL = 10001;
    //元素风格类型，用于填写
    const ELEMENT_STYLE_GARDENIA = 'gardenia';

    protected $tableHead = [];
    protected $data = [];
    protected $headToolbox = [];
    protected $extraJS = [];
    protected $extraLayuiJS = [];
    protected $tableAttrList = [];
    protected $operateTopButtonList = [];
    protected $colOperateBtnList = [];
    protected $primaryKey = 'id';
    protected $deleteTip = '您确定要删除么？';
    protected $isLoadJquery = true;
    //layui js模板数组
    protected $layuiTemplateList = [];

    /**
     * 设置数据集中的主键字段
     * @param string $primaryKey 主键名
     */
    public function setPrimaryKey($primaryKey = 'id') {
        $this->primaryKey = $primaryKey;
    }

    /**
     * Layui数据表格的基础参数
     * @param string $attr 属性名
     * @param mixed $value 属性值
     * @return $this
     * @throws \Exception
     */
    public function setTableAttr($attr,$value) {
        if (!isset($attr) || !isset($value)) {
            throw new \Exception('调用setTableAttr方法时attr和value参数必填！',self::CODE_FAIL);
        }
        $notSupportAttrList = ['toolbar','elem','cols'];
        if (in_array($attr,$notSupportAttrList)) {
            throw new \Exception('setTableAttr方法不支持'.implode('，',$notSupportAttrList).'属性，请修改。',self::CODE_FAIL);
        }
        $this->tableAttrList[$attr] = $value;
        return $this;
    }

    /**
     * 添加layui数据表格cols表头参数
     * @param string $field 字段名
     * @param string $title 标题名称
     * @param array $cols cols表头其它参数
     * @return $this
     * @throws Exception
     */
    public function addTableHead($field,$title,$cols = []) {
        if (gettype($cols) !== 'array') {
            throw new Exception('cols参数必须是关联数组格式',self::CODE_FAIL);
        }
        $temp = ['field'=> $field, 'title'=> $title];
        $temp = array_merge($temp,$cols);

        $this->tableHead[] = $temp;
        return $this;
    }

    /**
     * 批量添加layui数据表格cols表头参数
     * @param \string[][] $headList
     * @return $this
     * @throws Exception
     */
    public function addTableHeadBatch($headList = [['field'=> 'test', 'title'=> '测试']]) {
        foreach ($headList as $item) {
            if (!isset($item['field']) || !isset($item['title'])){
                throw new Exception('addListHeadBatch方法的headList参数中，field和title必填',self::CODE_FAIL);
            }
        }

        $this->tableHead = $headList;
        return $this;
    }

    /**
     * 添加layui数据表格操作列的按钮
     * @param string $field 按钮的name属性，相当于html元素的name属性
     * @param string $title 按钮标题
     * @param string $type 按钮风格，值为gardenia或者normal，用于指定元素是gardenia预置的还是用户自己定义的，后面将改为$style
     * @param string $btnType 按钮类型，值为增删改查 分别是create、delete、edit、read
     * @param array $attrList 属性列表，相当于html元素的属性
     * @param array $dataList data属性列表，相当于html元素的data属性的值
     * @param string $ruleName 规则名称，用于权限校验时显示或者隐藏本按钮
     * @return $this
     * @throws Exception
     */
    public function addColumnOperateButton($field,$title,$type,$btnType,$attrList = [],$dataList = [],$ruleName = '') {
        if (!$this->tableHead){
            throw new Exception('请先调用addListHead方法',self::CODE_FAIL);
        }
        $request = request();
        foreach ($this->tableHead as $item) {
            if ($item['field'] === $field) {
                if (isset($item['toolbar'])) {
                    throw new Exception('addColumnOperateButton方法和addListHead方法中设置toolbar属性不可同时存在，请不要设置toolbar',self::CODE_FAIL);
                };
            }
        }
        if ($ruleName) {
            if ($request->user['admin_type'] !== GardeniaConstant::GROUP_TYPE_SUPER_ADMIN){
                if (!in_array($ruleName,$request->user['access_list'])){
                    $attrList['style'] = 'display: none';
                }
            }
        }

        $this->colOperateBtnList[$field][] = [
            'field' => $field,
            'title' => $title,
            'type' => $type,
            'btnType' => $btnType,
            'attrList' => $attrList,
            'dataList' => $dataList,
        ];
        return $this;
    }

    /**
     * 设置layui数据表格的toolbar属性
     * @param string $element 元素选择器
     * @param string $type 类型：text或path，其中path是js文件的路径
     * @param string $content 内容
     * @return $this
     */
    public function setHeadToolbox($element = '#toolbarDemo',$type = 'text',$content = ""){

        $this->headToolbox = [
            'element' => $element,
            'type' => $type,

            'content' => $content,
        ];

        return $this;
    }

    /**
     * 添加额外的js
     * @param string $type:添加js的类型，可使用路径引入，也可以直接写js引入。若是前者，则值应当为即 path，否则为 text
     * @param string $content:若$type的值为 path时，值为相对于入口文件的相应的JS路径;若参数$type的值为 text时，则值如<script>alert('1');</script>
     * @return $this
     */
    public function addExtraJS($type,$content) {
        if ($type === null || $content === null){
           throw new \Exception('调用addExtraJS方法时，type和content参数必填',self::CODE_FAIL);
        }

        $this->extraJS[] = ['type' => $type , 'content' => $content ];
        return $this;
    }

    /**
     * 在layui完成加载各模块的后的回调函数离执行的js
     * @param string $type text或path，其中path是js文件的路径
     * @param string $content 内容
     * @return $this
     * @throws \Exception
     */
    public function addExtraLayuiJS($type,$content) {
        if ($type === null || $content === null){
            throw new \Exception('调用addExtraLayuiJS方法时，type和content参数必填',self::CODE_FAIL);
        }

        $this->extraLayuiJS[] = ['type' => $type , 'content' => $content ];
        return $this;
    }

    /**
     * 添加表格顶部按钮，用法与addColumnOperateButton类似
     * @param string $type 按钮风格，值为'gardenia','normal'
     * @param string $title 按钮标题
     * @param string $btnType 按钮类型，值为'create','delete'
     * @param array $attrList 属性列表
     * @return $this
     * @throws \Exception
     */
    public function addTopOperateButton($type,$title,$btnType = null,$attrList = []) {
        if ($title === null || $type === null){
            throw new \Exception('调用addTopOperateButton方法时，title,type参数必填',self::CODE_FAIL);
        }
        $supportTypeList = ['gardenia','normal'];
        if (!in_array($type,$supportTypeList)){
            throw new \Exception('调用addTopOperateButton方法时，type参数的值只支持'.implode(',',$supportTypeList),self::CODE_FAIL);
        }

        $temp = ['type'=> $type,'title'=> $title, 'attrList'=> $attrList];
        if ($btnType !== null) {
            $supportBtnTypeList = ['create','delete'];
            if (!in_array($btnType,$supportBtnTypeList)){
                throw new \Exception('调用supportBtnTypeList方法时，btnType参数的值只支持'.implode(',',$supportBtnTypeList),self::CODE_FAIL);
            }
            $temp['btnType'] = $btnType;
        }

        $this->operateTopButtonList[] = $temp;

        return $this;
    }

    /**
     * 是否加载jquery
     * @param bool $isLoadJquery
     * @return $this
     */
    public function loadJquery($isLoadJquery = true) {
        $this->isLoadJquery = $isLoadJquery;
        return $this;
    }

    /**
     * 设置删除时的提示文字
     * @param string $tip
     * @return $this
     */
    public function setDeleteTip($tip = '您确定要删除么？') {
        $this->deleteTip = $tip;
        return $this;
    }

    /**
     * 加载指定的模板文件
     * @param string $templatePath 模板路径
     * @param array $var 同tp的view助手函数的var参数用法一样
     */
    public function view($templatePath = '',$var = []) {
        $templatePath = $templatePath ? $templatePath : app_path().config('view.view_dir_name')
            .'/'.config('route.default_controller').'/'.config('route.default_action').
            '.'.config('view.view_suffix');

        view(dirname(__FILE__).'/../../tpl/index.html',[
            'display_type'=> 'other',
            'templateContent'=> view($templatePath,$var)->getContent()
        ])->send();
    }

    /**
     * 将GardeniaList类的数据传到指定模板，并渲染该模板
     */
    public function display() {
        //处理一些默认值
        if (!isset($this->tableAttrList['height'])) {
            $this->tableAttrList['height'] = 312;
        }

        //在渲染之前将通过addColumnOperateButton方法添加的操作按钮信息的模板ID关联到cols表头信息里
        $count = 0;
        foreach ($this->colOperateBtnList as $key => $btnList) {

            foreach ($this->tableHead as &$listHead){
                if ($key === $listHead['field']) {
                    $temp = '';
                    foreach ($btnList as $item) {
                        if ($item['type'] === 'gardenia'){
                            $attrStr = '';
                            if ($item['attrList']){
                                foreach ($item['attrList'] as $a_key => $value) {
                                    $attrStr =  $attrStr ? $attrStr.' '.$a_key.'="'.$value.'"' : $a_key.'="'.$value.'"';
                                }
                            }

                            $dataStr = '';
                            if ($item['dataList']){
                                foreach ($item['dataList'] as $d_key => $value) {
                                    $dataStr =  $dataStr ? $dataStr.' data-'.$d_key.'="'.$value.'"' : 'data-'.$d_key.'="'.$value.'"';
                                }
                            }

                            switch ($item['btnType']) {
                                case 'read':
                                    $temp = $temp.'<button name="'.GardeniaConstant::GARDENIA_PREFIX.'read" lay-event="'.GardeniaConstant::GARDENIA_PREFIX.'read" class="layui-btn layui-btn-warm layui-bg-green layui-btn-sm" '.$attrStr.' '.$dataStr.'>'.$item['title'].'</button>';
                                    break;
                                case 'edit':
                                    $temp = $temp.'<button name="'.GardeniaConstant::GARDENIA_PREFIX.'edit" lay-event="'.GardeniaConstant::GARDENIA_PREFIX.'edit" class="layui-btn layui-btn-warm layui-bg-blue layui-btn-sm" '.$attrStr.' '.$dataStr.'>'.$item['title'].'</button>';
                                    break;
                                case 'delete':
                                    $temp = $temp.'<button name="'.GardeniaConstant::GARDENIA_PREFIX.'delete" lay-event="'.GardeniaConstant::GARDENIA_PREFIX.'delete" class="layui-btn layui-btn-warm layui-bg-red layui-btn-sm" '.$attrStr.' '.$dataStr.'>'.$item['title'].'</button>';
                                    break;
                                default:
                                    new Exception('addColumnOperateButton不支持'.$item['btnType'].'按钮类型',self::CODE_FAIL);
                            }
                        }
                    }
                    $listHead['toolbar'] = '#colOperateBtnTemplateId_'.$key;
                    $this->layuiTemplateList[] = '<script type="text/html" id="'.'colOperateBtnTemplateId_'.$key.'">'.$temp.'</script>';
                }
            }
        }


        $subArray = [
            'tableHead'=> $this->tableHead,
            'primaryKey' => $this->primaryKey,
            'headToolbox' => $this->headToolbox,
            'extraJS' => $this->extraJS,
            'extraLayuiJS' => $this->extraLayuiJS,
            'tableAttrList' => $this->tableAttrList,
            'operateTopButtonList' => $this->operateTopButtonList,
            'isLoadJquery' => $this->isLoadJquery,
            'deleteTip' => $this->deleteTip,
            'colOperateBtnList' => $this->colOperateBtnList,
            'layuiTemplateList' => $this->layuiTemplateList,
        ];

        $mainArray = [
            'display_type'=> 'list',
            'templateContent'=> view(dirname(__FILE__).'/../../tpl/list.html',$subArray)->getContent(),
        ];
        view(dirname(__FILE__).'/../../tpl/index.html',$mainArray)->send();
    }
}