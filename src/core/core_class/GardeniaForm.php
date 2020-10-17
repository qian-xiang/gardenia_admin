<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace gardenia_admin\src\core\core_class;



use think\Exception;
use think\facade\View;

/**
 * Class GardeniaForm 表单生成类
 * @package gardenia_admin\src\core\core_class
 */
class GardeniaForm
{
    //成功的状态码
    const CODE_SUCCESS = 10000;
    //失败的状态码
    const CODE_FAIL = 10001;

    protected $formItemList = [];
    protected $btnList = [];
    protected $formUrl = null;
    protected $formMethod = null;
    protected $treeItemJsArr = [];
    protected $formJsArr = [];
    protected $formStatus = true;
    protected $innerJs = [];
    protected $formWholeStyle = [];

    /**
     * 添加表单项
     * @param string $styleType 风格类型 'gardenia','normal'
     * @param string $type 元素类型，若styleType为'gardenia'，则为以下之一：hidden、text、number、password、select、checkbox、switch、radio、textarea、tree，若是styleType为normal，则为普通html标签名
     * @param string $field 属性名
     * @param string $title 元素标题
     * @param array $option 元素的选项数组（供select的option使用），用法是 [value => title]
     * @param array $attrList 元素的属性数组，同html元素的属性列表
     * @return $this
     * @throws Exception
     */
    public function addFormItem($styleType,$type,$field,$title,$option = null,$attrList = null) {
        $styleTypeList = ['gardenia','normal'];
        if (!in_array($styleType,$styleTypeList)) {
            throw new Exception('addFormItem方法的styleType参数只支持'.implode(',',$styleTypeList),self::CODE_FAIL);
        }


        $temp = [
            'field'=> $field,
            'title'=> $title,
            'styleType'=> $styleType,
            'type'=> $type,
        ];
        $option !== null && $temp['option'] = $option;
        $attrList !== null && $temp['attrList'] = $attrList;

        $this->formItemList[] = $temp;

        return $this;
    }

    /**
     * 设置表单的整体风格，目前仅支持给表单项添加冒号 ['colon' => true]
     * @param array $data
     * @return $this
     * @throws Exception
     */
    public function setFormWholeStyle($data = []) {
        if (gettype($data) !== 'array') {
            throw new Exception('setFormWholeStyle方法的参数只支持数组类型',self::CODE_FAIL);
        }
        $this->formWholeStyle = $data;
        return $this;
    }

    /**
     * 添加表单底部按钮
     * @param string $styleType
     * @param string $type
     * @param string $field
     * @param string $title
     * @param array $attrList
     * @return $this
     * @throws Exception
     */
    public function addBottomButton($styleType,$type,$field,$title,$attrList = null) {
        $typeList = ['submit','reset','cancel','normal'];
        if (!in_array($type,$typeList)){
            throw new Exception('addBottomButton方法的type参数只支持'.implode(',',$typeList).'类型',self::CODE_FAIL);
        }

        $temp = [
            'type'=> $type,
            'styleType'=> $styleType,
            'title'=> $title,
            'field'=> $field,
        ];

        $attrList !== null && $temp['attrList'] = $attrList;

        $this->btnList[] = $temp;

        return $this;
    }

    /**
     * 设置表单提交方式：GET、POST等等
     * @param string $method
     * @return $this
     */
    public function setFormMethod($method) {
        $this->formMethod = $method;
        return $this;
    }

    /**
     * 设置表单提交Url
     * @param string $url
     * @return $this
     */
    public function setFormUrl($url) {
        $this->formUrl = $url;
        return $this;
    }

    /**
     * 添加树形组件表单项内部的js
     * @param string $field
     * @param string $type
     * @param string $content
     * @return $this
     */
    public function addTreeItemJs($field,$type,$content) {
        $this->treeItemJsArr[] = [
            'field' => $field,
            'type' => $type,
            'content' => $content,
        ];
        return $this;
    }

    /**
     * @param string $field
     * @param string $type
     * @param string $content
     * @return $this
     */
    public function addFormJs($field,$type,$content) {
        $this->formJsArr[] = [
            'field' => $field,
            'type' => $type,
            'content' => $content,
        ];
        return $this;
    }

    /**
     * 设置layui.js调用各模块成功后的回调函数内部执行的js
     * @param string $type
     * @param string $content
     * @param array $paramList
     * @return $this
     */
    public function setInnerJs($type,$content,$paramList = []) {

        $this->innerJs = [
            'type' => $type,
            'content' => $content,
            'paramList' => $paramList,
        ];
        return $this;
    }

    /**
     * 设置表单状态，是否可提交，若为true之间挑战，否则不会跳转
     * @param bool $status true或者false
     * @return $this
     */
    public function setFormStatus($status) {
        $this->formStatus = $status;
        return $this;
    }

    /**
     * 获取内部js文件的内容
     * @return string
     */
    protected function getInnerJsContent() {
        if (isset($this->innerJs) && $this->innerJs) {
            if ($this->innerJs['type'] === 'path'){
                return view($this->innerJs['content'],$this->innerJs['paramList'])->getContent();
            } else {
                return View::display($this->innerJs['content'],$this->innerJs['paramList']);
            }
        }

    }

    /**
     * 给表单项添加冒号，如果有设置给表单项的标题添加冒号的条件，则添加，否则不添加
     */
    private function ItemAddColon() {
        if ($this->formWholeStyle && isset($this->formWholeStyle['colon'])){
            if ($this->formWholeStyle['colon']) {
                foreach ($this->formItemList as &$item) {
                    $item['title'] = $item['title'].'：';
                }
            }
        }
    }

    /**
     * 将相关数据加载到表单模板文件中并执行
     */
    public function display() {
        $request = request();
        $this->formUrl === null && $this->formUrl = url('/'.$request->controller().'/'.$request->action());
        $this->formMethod === null && $this->formMethod = 'POST';

        $innerJs = $this->getInnerJsContent();

        //如果有设置给表单项的标题添加冒号的条件，则添加
        $this->ItemAddColon();

        $subArray = [
            'data'=> $this->formItemList,
            'btnList'=> $this->btnList,
            'formUrl'=> $this->formUrl,
            'formMethod'=> $this->formMethod,
            'treeItemJsArr'=> $this->treeItemJsArr,
            'formJsArr'=> $this->formJsArr,
            'formStatus'=> $this->formStatus,
            'innerJs'=> $innerJs,
            'formWholeStyle'=> $this->formWholeStyle,
        ];

        $mainArray = [
            'display_type'=> 'form',
            'templateContent'=> view(dirname(__FILE__).'/../../tpl/form.html',$subArray)->getContent(),
        ];

        return view(dirname(__FILE__).'/../../tpl/index.html',$mainArray)->send();
    }
}