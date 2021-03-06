@extends('body')
@section('main')

    <section class="padLR1r">
        <!--填写内容-->
        <form action="ticketchange" method="POST" style="display: inline;">

        <!--姓名-->
        <div class="marTBd8r borderB">
            <p class="color2f font14">姓名</p>
            <input type="text" name="name" class="inputText marTBd8r" required="required" placeholder="一定要填，但不要求一定要真名" value="{{$ticket->name}}" />
        </div>
        <!--联系方式-->
        <div class="marTBd8r borderB">
            <p class="color2f font14">手机长号</p>
            <input type="tel" name="number" class="inputText marTBd8r" required="required" placeholder="一定要填" value="{{$ticket->number}}"/>
        </div>
        <div class="marTBd8r borderB">
            <p class="color2f font14">校园短号</p>
            <input type="tel" name="shortnum" class="inputText marTBd8r" placeholder="有的麻烦留一下" value="{{$ticket->shortnum}}"/>
        </div>
        <!--地址-->
        <div class="marTBd8r borderB">
            <p class="color2f font14">院区</p>
            <div class="marTBd8r font13 pr">
                <select class="selectDown" name="area">
                    <option value="{{$ticket->area}}">@if($ticket->area==0)东区@else 西区@endif</option>
                    <option value="0">东区</option>
                    <option value="1">西区</option>
                </select>
                <span class="downBtn"></span>
            </div>
        </div>
        <div class="marTBd8r borderB">
            <p class="color2f font14">宿舍号</p>
            <input type="text" name="address" class="inputText marTBd8r" required="required" placeholder="例如：H12" value="{{$ticket->address}}"/>
        </div>


        <div class="marTBd8r borderB font13 clearfix">
            <p class="color2f font14">空闲时间</p>
            <div class="dateDiv pr">
                <div class="marTBd8r in_block font13 pr selectDate" style="width: 34%;">
                    <select class="selectDown" name="date">
                        <option value="{{$ticket->date}}">
                        @if($ticket->date==1)星期一
                        @elseif($ticket->date==2) 星期二
                        @elseif($ticket->date==3) 星期三
                        @elseif($ticket->date==4) 星期四
                        @elseif($ticket->date==5) 星期五
                        @endif</option>
                        <option value="1">星期一</option>
                        <option value="2">星期二</option>
                        <option value="3">星期三</option>
                        <option value="4">星期四</option>
                        <option value="5">星期五</option>
                    </select>
                    <span class="downBtn"></span>
                </div>
                <div class="marTBd8r in_block font13 pr selectDate" style="width: 44%;">
                    <select class="selectDown" name="hour">
                        <option value="{{$ticket->hour}}">{{$ticket->hour}}</option>
                        <option value="6点后">6点后</option>
                        <option value="6点半后">6点半后</option>
                        <option value="7点后">7点后</option>
                        <option value="7点前">7点前</option>
                        <option value="8点后">8点后</option>
                        <option value="9点后">9点后</option>
                        <option value="整晚都可以">整晚都可以</option>
                    </select>
                    <span class="downBtn" ></span>
                </div>
                <span class="addBtn" style="display:none">+</span>
                @if($ticket->date1) 

                <div class="dateDiv pr">
                    <div class="marTBd8r in_block font13 pr selectDate" style="width: 34%;">
                        <select class="selectDown" name="date">
                            <option value="{{$ticket->date1}}">
                            @if($ticket->date1==1)星期一
                            @elseif($ticket->date1==2) 星期二
                            @elseif($ticket->date1==3) 星期三
                            @elseif($ticket->date1==4) 星期四
                            @elseif($ticket->date1==5) 星期五
                            @endif</option>
                            <option value="1">星期一</option>
                            <option value="2">星期二</option>
                            <option value="3">星期三</option>
                            <option value="4">星期四</option>
                            <option value="5">星期五</option>
                        </select>
                        <span class="downBtn"></span>
                    </div>
                    <div class="marTBd8r in_block font13 pr selectDate" style="width: 44%;">
                        <select class="selectDown" name="hour">
                            <option value="{{$ticket->hour1}}">{{$ticket->hour1}}</option>
                            <option value="6点后">6点后</option>
                            <option value="6点半后">6点半后</option>
                            <option value="7点后">7点后</option>
                            <option value="7点前">7点前</option>
                            <option value="8点后">8点后</option>
                            <option value="9点后">9点后</option>
                            <option value="整晚都可以">整晚都可以</option>
                        </select>
                        <span class="downBtn"></span>
                    </div>
                <span class="deleteBtn" onclick="deleteDate(this)">-</span>
                @else 
                <span class="addBtn">+</span>
                @endif
            </div>
            <P style="color: red;">PS:为了避免人手不足造成的不便，空闲时间可以多留一个</P>
        </div>

        <!--报修内容-->
        <div class="marTBd8r borderB">
            <p class="color2f font14">报修内容</p>
            <textarea rows="5" name="problem" required="required" class="multiInput marTBd8r font13" placeholder="请输入报修内容">{{$ticket->problem}}</textarea>
        </div>
        <input type="submit" value="修改订单" class="mainBtn marTBd8r font14 color2f">
        </form>
    </section>

    <script type="text/javascript" charset="utf-8">
        var dateHtml = '<div class="dateDiv pr"><div class="marTBd8r in_block font13 pr selectDate" style="width: 34%;"><select class="selectDown" name="date1"><option value="1">星期一</option><option value="2">星期二</option><option value="3">星期三</option><option value="4">星期四</option><option value="5">星期五</option></select><span class="downBtn"></span></div>&nbsp;<div class="marTBd8r in_block font13 pr selectDate" style="width: 44%;"><select class="selectDown" name="hour1"><option value="6点后">6点后</option><option value="6点半后">6点半后</option><option value="7点后">7点后</option><option value="7点前">7点前</option><option value="8点后">8点后</option><option value="9点后">9点后</option><option value="整晚都可以">整晚都可以</option></select><span class="downBtn"></span></div><span class="deleteBtn" onclick="deleteDate(this)">-</span></div>'
        $('.addBtn').bind('click',function(){ 
            $(this).parent().after(dateHtml);
            $(".addBtn").hide();
        });
        function deleteDate(obj){ 
            $(obj).parent().remove();
            $(".addBtn").show();
        }
    </script>
  <div class="row-fluid">
    <div class="span12">
      <p class="text-center">
        © 2016 中大南方PC服务队 | Powered by JokerLinly
      </p>
    </div>
  </div>
@stop