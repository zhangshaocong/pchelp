<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use DB,Redirect, Input,Validator,Session;
use \View;
use App\Ticket;
use App\Pcadmin;
use App\Wcuser;
use App\Pcer;
use App\Idle;
use App\Comment;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use EasyWeChat;

class TicketController extends Controller
{
    public function ticketlock($id)
    {
       $pcadmin_id = Session::get('pcadmin_id');
       $ispcadmin = Ticket::find($id)->pcadmin_id;
       if ($ispcadmin) {
           $res = Ticket::where('id',$id)->update(['pcadmin_id' => Null]);
           if ($res) {
               return "unlock";
           } else {
               return "网络异常！";
           }          
       }else{ 
           $res = Ticket::where('id',$id)->update(['pcadmin_id' => $pcadmin_id]);
           if ($res) {
               return "lock";
           } else {
               return "网络异常！";
           }
           
       }
    }

    public function pcersingle()
    {
      $pcadmin_id = Session::get('pcadmin_id');
      if (Input::get('pcer_id')) {
        $res = Ticket::where('id',Input::get('id'))->update(['pcer_id'=>Input::get('pcer_id'),'pcadmin_id'=>$pcadmin_id]);
        if ($res) {
            return Redirect::back();
        } else {
            return Redirect::back()->with('message', '网络异常');
        }
          
      } else {
        return Redirect::back();
      }
        
    }

    public function getUnsent()
    {
      $pcadmin_id = Session::get('pcadmin_id');
      $tickets = Ticket::where('pcadmin_id',$pcadmin_id)
                        ->where('state',0)
                        ->whereNotNull('pcer_id')
                        ->orderBy('pcer_id','DESC')
                        ->with(['pcer'])->get();
      return view::make('Admin.mytickets_unsent',['tickets'=>$tickets]);
    }

    public function ticketsunset()
    {
      $pcadmin_id = Session::get('pcadmin_id');
      $tickets = Ticket::where('pcadmin_id',$pcadmin_id)
                        ->where('state',0)
                        ->whereNull('pcer_id')->get();
      $tpcers = Idle::where('date',date("w"))->with('pcer')->get();
      return view::make('Admin.mytickets_unset',['tickets'=>$tickets,'pcadmin_id'=>$pcadmin_id,'tpcers'=>$tpcers]);
    }

    public function getUnfinish()
    {
      $pcadmin_id = Session::get('pcadmin_id');
      $tickets = Ticket::where('pcadmin_id',$pcadmin_id)
                        ->where('state',1)
                        ->whereNotNull('pcer_id')
                        ->orderBy('created_at','ASC')
                        ->with(['pcer','wcuser'])->get();
      return view::make('Admin.mytickets_unfinish',['tickets'=>$tickets]);
    }

    public function getFinish()
    {
      $pcadmin_id = Session::get('pcadmin_id');
      $tickets = Ticket::where('pcadmin_id',$pcadmin_id)
                        ->where('state',2)
                        ->whereNotNull('pcer_id')
                        ->orderBy('created_at','DESC')
                        ->with(['pcer','wcuser'])->get();
      return view::make('Admin.mytickets_finish',['tickets'=>$tickets]);
    }

    public function postUnlock()
    {
      $ticket_id = Input::get('id');
      $isunlock = Ticket::find($ticket_id)->update(['pcer_id' => Null,'pcadmin_id' =>Null]);
      if($isunlock){
        return Redirect::back();
      }else{
        return Redirect::back()->with('message', '网络异常');
      }
    }

    public function postSent()
    {
      $ticket_id = Input::get('id');

      $istate = Ticket::find($ticket_id)->update(['state' => '1']);
      if ($istate) {
          $ticket = Ticket::where('id',$ticket_id)
                          ->with('wcuser')
                          ->with('pcadmin')
                          ->with(['pcer'=>function($query){
                            $query->with('wcuser');
                        }])->first();

          if (date("w") == $ticket->date) {
            $hour = $ticket->hour;
          } elseif(date("w") == $ticket->date1) {
            $hour = $ticket->hour1;
          }else{
            $hour = "今晚";
          }
          
          /*
            发送给用户的模板消息        
           */
          $notice_user = EasyWeChat::notice();
          /*获取订单用户的openid*/
          $wcuser_openid = $ticket->wcuser->openid;
          $templateId_user = 'NSVoIDTtDGr5a2ECkWLZzkjHs6EiqDKsYC-vyB5N3BI';
          $url_user = "http://pc.nfu.edu.cn/mytickets/{$ticket->id}/show";
          $color = '#FF0000';
          $data_user = array(
                "first" => "PC管理员已经为你的订单分配了一个PC仔！",
                "keyword1" => $ticket->id."的订单内容为  ".$ticket->problem,
                "keyword2" => "具体上门时间是今晚".$hour,
                "remark"  => "听！手机铃声响起了！PC仔正朝您飞去！如果PC仔中途迷路了，请点击详情带它回家！",
              );

          $messageId = $notice_user->uses($templateId_user)->withUrl($url_user)->andData($data_user)->andReceiver($wcuser_openid)->send();

          /*
            发送给PC队员的模板消息        
           */
          if ($ticket->shortnum) {
              $shortnum = $ticket->shortnum;
          } else {
              $shortnum = "无";
          }

          if ($ticket->area == 0) {
              $area = "东区";
          }elseif ($ticket->area == 1) {
              $area = "西区";
          } else {
              $area = "";
          }
          

          /*获取PC队员的openid*/
          $pcer_openid = $ticket->pcer->wcuser->openid;
          $notice_pcer = EasyWeChat::notice();
          $templateId_pcer = 'aCZbEi9-JZbkR4otY8tkeFFV2zwf-lUFKFbos49h1Qc';
          $url_pcer = "http://pc.nfu.edu.cn/pcertickets/{$ticket->id}/show";
          // $color_pcer = '#FF0000';
          $data_pcer = array(
            "first"   => $ticket->pcadmin->pcer->name."给你分配了订单!请尽快跟进！辛苦了！",
            "keynote1" => $ticket->problem,
            "keynote2" => $area.$ticket->address.",".$hour,
            "remark"  => "长号：".$ticket->number.";短号：".$ticket->shortnum,
          );
          $comment = new Comment;
          $comment->ticket_id = $ticket->id;
          $comment->from = 4;
          $comment->text = "我给你分配了订单!请尽快跟进！辛苦了！";
          $comment->wcuser_id = $ticket->pcadmin->pcer->wcuser->id;
          $res = $comment->save();
          if (!$res) {
              return Redirect::back()->with('message', '网络异常');
          } 

          $messageId = $notice_pcer->uses($templateId_pcer)->withUrl($url_pcer)->andData($data_pcer)->andReceiver($pcer_openid)->send();
          
          return Redirect::back();
      } else {
        return Redirect::back()->with('message', '网络异常');
      }
    }

    public function getSentall()
    {
        $pcadmin_id = Session::get('pcadmin_id');
        $tickets = Ticket::where('pcadmin_id',$pcadmin_id)
                        ->where('state',0)
                        ->whereNotNull('pcer_id')
                        ->with('pcadmin')
                        ->with('wcuser')
                        ->with(['pcer'=>function($query){
                            $query->with('wcuser');
                        }])->get();
        $istate = Ticket::where('pcadmin_id',$pcadmin_id)
                        ->where('state',0)->update(['state' => '1']);
        if ($istate) {
            foreach ($tickets as $ticket) {

              if (date("w") == $ticket->date) {
                $hour = $ticket->hour;
              } elseif(date("w") == $ticket->date1) {
                $hour = $ticket->hour1;
              }else{
                $hour = "今晚";
              }
              
              /*
                发送给用户的模板消息        
               */
              $notice_user = EasyWeChat::notice();
              /*获取订单用户的openid*/
              $wcuser_openid = $ticket->wcuser->openid;
              $templateId_user = 'NSVoIDTtDGr5a2ECkWLZzkjHs6EiqDKsYC-vyB5N3BI';
              $url_user = "http://pc.nfu.edu.cn/mytickets/{$ticket->id}/show";
              $color = '#FF0000';
              $data_user = array(
                "first" => "PC管理员已经为你的订单分配了一个PC仔！",
                "keyword1" => $ticket->id."的订单内容为  ".$ticket->problem,
                "keyword2" => "具体上门时间是今晚".$hour,
                "remark"  => "听！手机铃声响起了！PC仔正朝您飞去！如果PC仔中途迷路了，点击详情带它回家！",
              );

              $messageId = $notice_user->uses($templateId_user)->withUrl($url_user)->andData($data_user)->andReceiver($wcuser_openid)->send();

              /*
                发送给PC队员的模板消息        
               */
              if ($ticket->shortnum) {
                  $shortnum = $ticket->shortnum;
              } else {
                  $shortnum = "无";
              }

              if ($ticket->area == 0) {
                  $area = "东区";
              }elseif ($ticket->area == 1) {
                  $area = "西区";
              } else {
                  $area = "";
              }
              
              $pc_wcuserid = Pcer::where('id',$ticket->pcer_id)->first()->wcuser_id;
              /*获取PC队员的openid*/
              $pcer_openid = $ticket->pcer->wcuser->openid;
              $notice_pcer = EasyWeChat::notice();
              $templateId_pcer = 'aCZbEi9-JZbkR4otY8tkeFFV2zwf-lUFKFbos49h1Qc';
              $url_pcer = "http://pc.nfu.edu.cn/pcertickets/{$ticket->id}/show";
              // $color_pcer = '#FF0000';
              $data_pcer = array(
                "first"   => $ticket->pcadmin->pcer->name."给你分配了订单!请尽快跟进！辛苦了！",
                "keynote1" => $ticket->problem,
                "keynote2" => $area.$ticket->address.",".$hour,
                "remark"  => "长号：".$ticket->number.";短号：".$ticket->shortnum,
              );
              $comment = new Comment;
              $comment->ticket_id = $ticket->id;
              $comment->from = 4;
              $comment->text = "我给你分配了订单!请尽快跟进！辛苦了！";
              $comment->wcuser_id = $ticket->pcadmin->pcer->wcuser->id;
              $res = $comment->save();
              if (!$res) {
                  return Redirect::back()->with('message', '网络异常');
              } 

              $messageId = $notice_pcer->uses($templateId_pcer)->withUrl($url_pcer)->andData($data_pcer)->andReceiver($pcer_openid)->send();
            }
            return Redirect::back();
        } else {
            return Redirect::back()->with('message', '网络异常');
        }
    }

    public function beforeset()
    {
      $pcadmin_id = Session::get('pcadmin_id');
      if (Input::get('pcer_id')) {
        $ticket_id = Input::get('id');
        $pcer_id = Input::get('pcer_id');
        $istoday = Ticket::where('id',$ticket_id)->first();
        $isday = Idle::where('pcer_id',$pcer_id)->Where(function($query)use($istoday){
          $query->where('date',$istoday->date)->orwhere('date',$istoday->date1);
        })->get();
        
        if (count($isday)!=0) {
          $istate = Ticket::where('id',$ticket_id)->update(['state' => '1','pcer_id'=>$pcer_id]);
          if ($istate) {
            $ticket = Ticket::where('id',$ticket_id)
                            ->with('wcuser')
                            ->with('pcadmin')
                            ->with(['pcer'=>function($query){
                              $query->with('wcuser');
                          }])->first();

            if (date("w") == $ticket->date) {
              $hour = $ticket->hour;
            } elseif(date("w") == $ticket->date1) {
              $hour = $ticket->hour1;
            }else{
              $hour = "今晚";
            }
            
            /*
              发送给用户的模板消息        
             */
            $notice_user = EasyWeChat::notice();
            /*获取订单用户的openid*/
            $wcuser_openid = $ticket->wcuser->openid;
            $templateId_user = 'NSVoIDTtDGr5a2ECkWLZzkjHs6EiqDKsYC-vyB5N3BI';
              $url_user = "http://pc.nfu.edu.cn/mytickets/{$ticket->id}/show";
              $color = '#FF0000';
              $data_user = array(
                "first" => "PC管理员已经为你的订单分配了一个PC仔！",
                "keyword1" => $ticket->id."的订单内容为  ".$ticket->problem,
                "keyword2" => "具体上门时间是今晚".$hour,
                "remark"  => "听！手机铃声响起了！PC仔正朝您飞去！如果PC仔中途迷路了，点击详情带它回家！",
              );

            $messageId = $notice_user->uses($templateId_user)->withUrl($url_user)->andData($data_user)->andReceiver($wcuser_openid)->send();

            /*
              发送给PC队员的模板消息        
             */
            if ($ticket->shortnum) {
                $shortnum = $ticket->shortnum;
            } else {
                $shortnum = "无";
            }

            if ($ticket->area == 0) {
                $area = "东区";
            }elseif ($ticket->area == 1) {
                $area = "西区";
            } else {
                $area = "";
            }
            

            /*获取PC队员的openid*/
            $pcer_openid = $ticket->pcer->wcuser->openid;
            $notice_pcer = EasyWeChat::notice();
            $templateId_pcer = 'aCZbEi9-JZbkR4otY8tkeFFV2zwf-lUFKFbos49h1Qc';
            $url_pcer = "http://pc.nfu.edu.cn/pcertickets/{$ticket->id}/show";
            // $color_pcer = '#FF0000';
            $data_pcer = array(
              "first"   => $ticket->pcadmin->pcer->name."给你分配了订单!请尽快跟进！辛苦了！",
              "keynote1" => $ticket->problem,
              "keynote2" => $area.$ticket->address.",".$hour,
              "remark"  => "长号：".$ticket->number.";短号：".$ticket->shortnum,
            );
            $comment = new Comment;
            $comment->ticket_id = $ticket->id;
            $comment->from = 4;
            $comment->text = "我给你分配了订单!请尽快跟进！辛苦了！";
            $comment->wcuser_id = $ticket->pcadmin->pcer->wcuser->id;
            $res = $comment->save();
            if (!$res) {
                return Redirect::back()->with('message', '网络异常');
            }

            $messageId = $notice_pcer->uses($templateId_pcer)->withUrl($url_pcer)->andData($data_pcer)->andReceiver($pcer_openid)->send();
            return Redirect::back();
          } else {
            return Redirect::back()->with('message', '网络异常');
          }
        } else {
          return Redirect::back()->with('message', '该队员值班时间与订单上要求的时间不符合！');
        }
        
        
      } else {
        return Redirect::back()->with('message', '请选择维修员！');
      }
      
    }

    public function getTicketslist($openid)
    {
      $wcuser = Wcuser::where('openid',$openid)->where('state',2)->first();
      if ($wcuser) {
        $pcadmin = Pcadmin::with(['pcer'=>function($query)use($wcuser){
            $query->where('wcuser_id',$wcuser->id);
        }])->first();
        if ($pcadmin) {

          $tickets = Ticket::where('pcadmin_id',$pcadmin->id)->whereNotNull('pcer_id')->with('pcer')->get();
          return view::make('Admin.list',['tickets'=>$tickets]);
        }else
          return view::make('jurisdiction');
      } else {
        return view::make('jurisdiction');
      }
    }

    public function getAdminticketdata($value='')
    {
      # code...
    }

}
