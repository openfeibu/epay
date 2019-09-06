<?php
namespace com\weimifu\page;
echo'<div style="text-align: center;">
                    <ul class="pagination">';
$json = array(
    "total_page" => $pages,
    "now_page"   => $page,
    "self_url"   => $self_url,
    "link"       => $link,
    "plus"       => "5",
);
$page = new page_size($json);
echo $page->page_return();
echo "</ul></div>";

class page_size{
    public $total_page;                                                         //总页面
    public $now_page;                                                           //当前页面
    public $self_url;                                                           //前面的url
    public $link;                                                               //后面链接的参数
    public $plus;                                                               //分页偏移量

    function __construct($json){
        foreach($json as $k => $v){
            $this->$k = $v;
        }
    }

    function page_return(){
        $first = 1;                                                             //第一页
        $prev = $this->now_page - 1;                                            //上一页
        $next = $this->now_page + 1;                                            //下一页
        $last = $this->total_page;                                              //尾页
        $begin = 1;                                                             //显示起始页
        if($this->now_page + $this->plus > $this->total_page && $this->total_page > (1 + $this->plus * 2)){      //当前页加偏移量大于总页数且总页数不小于两倍的偏移量加1
            $begin = $this->total_page - $this->plus * 2;
        }elseif($this->total_page < (1 + $this->plus * 2) || $this->now_page - $this->plus <= 0){           //总页数小于两倍的偏移量加1或当前页减偏移量小于等于0
            $begin = 1;
        }else{
            $begin = $this->now_page - $this->plus;
        }
        $return = "";                                                           //定义的返回数据变量
        $return .= "<li>
            <span style='position: relative;float: left;padding: 6px 12px;margin-left: -1px;line-height: 1.42857143;color: #777;text-decoration: none;background-color: #fff;border: 0px solid #ddd;'>共&nbsp;&nbsp;{$this->total_page}&nbsp;&nbsp;页</span></li>
        <li>
        <input type='text' id='tiao_now_page' value='$this->now_page' style='    position: relative; float: left; padding: 6px 12px;  margin-left: -1px;line-height: 1.42857143;color: #777;text-decoration: none;background-color: #fff;border: 1px solid #ddd;width: 50px;margin-right: 10px;'>
        <a href=\"javascript:tiao_now_page(".$this->total_page.",'".$this->self_url."','".$this->link."')\" style='margin-right: 10px;'>跳转</a>
        </li>";
        if($this->now_page != 1 && $this->total_page != 0){                       //当初始页不等于0且不等于1的时候，首页下一页可点击，否则就不
            $return .= "<li><a href='{$this->self_url}?page={$first}{$this->link}'>首页</a></li>";
            $return .= "<li><a href='{$this->self_url}?page={$prev}{$this->link}'>&laquo;</a></li>";
        }else{
            $return .= "<li class='disabled'><a>首页</a></li>";
            $return .= "<li class='disabled'><a>&laquo;</a></li>";
        }
        if($this->total_page == 0){                                               //总页数等于0输出不可点击的1
            $return .= "<li class='disabled'><a>1</a></li>";
        }elseif($this->total_page < (1 + $this->plus * 2)){                           //总页数小于两倍的偏移量加1，执行i小于等于total_page的操作，否则执行i小于等于初始页加两倍的偏移量的操作
            for($i = $begin; $i <= $this->total_page; $i++){
                if($i == $this->now_page){
                    $return .= "<li class='disabled'><a>{$i}</a></li>";
                }else{
                    $return .= "<li><a href='{$this->self_url}?page={$i}{$this->link}'>{$i}</a></li>";
                }
            }
        }else{
            for($i = $begin; $i <= ($begin + $this->plus * 2); $i++){
                if($i == $this->now_page){
                    $return .= "<li class='disabled'><a>{$i}</a></li>";
                }else{
                    $return .= "<li><a href='{$this->self_url}?page={$i}{$this->link}'>{$i}</a></li>";
                }
            }
        }
        if($this->now_page == $this->total_page || $this->total_page == 0){       //当前页不等于总页面数或者总页面数不等于0的时候，尾页上一页不可点击，否则就可
            $return .= "<li class='disabled'><a>&raquo;</a></li>";
            $return .= "<li class='disabled'><a>尾页</a></li>";
        }else{
            $return .= "<li><a href='{$this->self_url}?page={$next}{$this->link}'>&raquo;</a></li>";
            $return .= "<li><a href='{$this->self_url}?page={$last}{$this->link}'>尾页</a></li>";
        }
        $return .= '<script>function tiao_now_page(pages,self_url,link){var page = document.getElementById("tiao_now_page").value;page = parseInt(page);if(page<=pages){location.href = self_url+"?page="+page+link;}}</script>';
        return $return;                                                         //返回数据
    }
}

?>
