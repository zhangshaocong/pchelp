@extends('body')
@section('main')

    <section class="padLR1r">
        <!--头像-->
        <span class="headIco"><img src="<?php echo $headimgurl;?>" class="img-circle img-responsive center-block" alt=""></span>

        <!--填写内容-->
        <form action="sign" method="POST" style="display: inline;">
        <input type="hidden" name="wcuser_id" value="{{$wcuser_id}}" >
        <!--姓名-->
        <div class="marTBd8r borderB">
            <p class="color2f font14">姓名</p>
            <input type="text" name="name" class="inputText marTBd8r" placeholder="真实姓名" required="required"/>
        </div>
        <!--年级-->
        <div class="marTBd8r borderB">
            <p class="color2f font14">年级</p>
            <div class="marTBd8r font13 pr">
                <select class="selectDown" name="school_level">
                    <option value="15">2015级</option>
                    <option value="14">2014级</option>
                    <option value="13">2013级</option>
                    <option value="12">2012级</option>
                    
                </select>
                <span class="downBtn"></span>
            </div>
        </div>
        <!--学号-->
        <div class="marTBd8r borderB">
            <p class="color2f font14">学号</p>
            <input type="number" name="school_id" class="inputText marTBd8r" required="required" placeholder="例如：122011137"/>
        </div>
        <!--学系-->
        <div class="marTBd8r borderB">
            <p class="color2f font14">学系</p>
            <input type="text" name="department" class="inputText marTBd8r" placeholder="例如：电子通信与软件工程系"/>
        </div>
        <!--专业-->
        <div class="marTBd8r borderB">
            <p class="color2f font14">专业</p>
            <input type="text" name="major" class="inputText marTBd8r" placeholder="例如：计算机科学与技术"/>
        </div>
        <!--班级-->
        <div class="marTBd8r borderB">
            <p class="color2f font14">班级</p>
            <input type="text" name="clazz" class="inputText marTBd8r" placeholder="例如：计算机3班"/>
        </div>
        <!--联系方式-->
        <div class="marTBd8r borderB">
            <p class="color2f font14">手机长号</p>
            <input type="number" name="long_number" class="inputText marTBd8r" required="required" placeholder="一定要填"/>
        </div>
        <div class="marTBd8r borderB">
            <p class="color2f font14">校园短号</p>
            <input type="number" name="number" class="inputText marTBd8r"  placeholder="有的麻烦留一下"/>
        </div>
        <!--地址-->
        <div class="marTBd8r borderB">
            <p class="color2f font14">院区</p>
            <div class="marTBd8r font13 pr">
                <select class="selectDown" name="area">
                    <option value="0">东区</option>
                    <option value="1">西区</option>
                </select>
                <span class="downBtn"></span>
            </div>
        </div>
        <div class="marTBd8r borderB">
            <p class="color2f font14">宿舍号</p>
            <input type="text" name="address" class="inputText marTBd8r" required="required" placeholder="例如：H12"/>
        </div>
        <P style="color:red;">PS:以上内容请正确填写，否则无法通过审核</P>
        <input type="submit" class="mainBtn marTBd8r font14 color2f">
        </form>

  <div class="row-fluid">
    <div class="span12">
      <p class="text-center">
        © 2016 中大南方PC服务队 | Powered by JokerLinly
      </p>
    </div>
  </div>
@stop