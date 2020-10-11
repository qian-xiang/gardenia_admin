<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace gardenia_admin\src\core\core_class;



use think\Exception;
use think\facade\View;

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
     * @param string $styleType:风格类型 'gardenia','normal'
     * @param string $type:类型
     * @param $field
     * @param $title
     * @param null $option
     * @param null $attrList
     *  @param false $enableColon:开启标题后加冒号
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
    public function setFormWholeStyle($data = []) {
        if (gettype($data) !== 'array') {
            throw new Exception('setFormWholeStyle方法的参数只支持数组类型',self::CODE_FAIL);
        }
        $this->formWholeStyle = $data;
        return $this;
    }
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
    public function setFormMethod($method) {
        $this->formMethod = $method;
        return $this;
    }
    public function setFormUrl($url) {
        $this->formUrl = $url;
        return $this;
    }
    public function addTreeItemJs($field,$type,$content) {
        $this->treeItemJsArr[] = [
            'field' => $field,
            'type' => $type,
            'content' => $content,
        ];
        return $this;
    }
    public function addFormJs($field,$type,$content) {
        $this->formJsArr[] = [
            'field' => $field,
            'type' => $type,
            'content' => $content,
        ];
        return $this;
    }
    public function setInnerJs($type,$content,$paramList = []) {

        $this->innerJs = [
            'type' => $type,
            'content' => $content,
            'paramList' => $paramList,
        ];
        return $this;
    }
    public function setFormStatus($status) {
        $this->formStatus = $status;
        return $this;
    }
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
     * 如果有设置给表单项的标题添加冒号的条件，则添加，否则不添加
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