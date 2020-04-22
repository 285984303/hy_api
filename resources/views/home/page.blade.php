<style>
    .page-div{
        clear: both;
    }
    .foot-fen{
        clear: both;

    }
    .page-head{
        display: inline-block;
        width: 50px;
        height: 30px;
        line-height: 30px;
        text-align: center;
        border: 1px solid #ddd;
        color: #666;
        float: left;
        margin-right: 5px;
    }
    .page-last{
        display: inline-block;
        width: 50px;
        height: 30px;
        line-height: 30px;
        text-align: center;
        border: 1px solid #ddd;
        color: #666;
        float: left;
    }
    
    .page-prev{
        display: inline-block;
        width: 90px;
        height: 30px;
        line-height: 30px;
        text-align: center;
        border: 1px solid #ddd;
        color: #666;
        float: left;
        margin-right: 5px;
    }
    .page-next{
        display: inline-block;
        width: 90px;
        height: 30px;
        line-height: 30px;
        text-align: center;
        border: 1px solid #ddd;
        color: #666;
        float: left;
        margin-right: 5px;
        margin-left: 5px;
    }
    .page-num{
        display: inline-block;
        width: 40px;
        height: 30px;
        line-height: 30px;
        text-align: center;
        border: 1px solid #ddd;
        color: #666;
        
    }
    .foot-fen input{
        display: block;
        float: right;
        width: 60px;
        height: 27px;
        line-height: 26px;
        border: 1px solid #ddd;
        margin-left: 10px;
    }
    .foot-fen span{
        color: #00CCFF;
        padding-left: 10px;
        display: block;
        float: right;
        line-height: 30px;
        cursor: pointer;
    }
    .foot-fen s{
        text-decoration: none;
        font-size: 18px;
        color: #00CCFF;
        padding: 0 10px;
    }
    .page-div2{
    max-width: 264px;
    text-overflow: ellipsis;		  
    overflow: hidden;
    white-space: nowrap;
    overflow: hidden;
    float: left;
    }
    .currentPage {
        color: #666;
        border: 1px solid #ddd;
        background-color:#efefef;
    }

</style>
<div style="height: 10px;clear: both"></div>
<div class="page-div">
    <div class="foot-fen">
        <a class="page-head" href="{{ $paginator->appends(Request::except(['page']))->url(1)}}">首页</a>
        <a class="page-prev" href="{{ $paginator->previousPageUrl()??'javascript:void(0)' }}">上一页</a>
        <div class="page-div2">
        @for($i=3;$i>=1;$i--)
            @if ($paginator->currentPage() - $i > 0)
                <a href="{{ $paginator->appends(Request::except(['page']))->url($paginator->currentPage() - $i) }}" class="page-num">{{ $paginator->currentPage() - $i }}</a>
            @endif
        @endfor
        <a href="javascript:void(0);" class="page-num currentPage">{{ $paginator->currentPage() }}</a>
        @for($i=$paginator->currentPage()+1;$i<=$paginator->lastPage();$i++)
            <a href="{{ $paginator->appends(Request::except(['page']))->url($i) }}" class="page-num">{{ $i }}</a>
        @endfor
        </div>
        <a class="page-next" href="{{ $paginator->nextPageUrl()??'javascript:void(0)' }}">下一页</a>
        <a class="page-last" href="{{ $paginator->appends(Request::except(['page']))->url($paginator->lastPage()) }}">末页</a>
        {{--<span>跳转页</span>--}}
        {{--<input type="text" />--}}
    </div>
</div>
