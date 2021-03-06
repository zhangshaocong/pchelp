<?php

namespace App\Http\Controllers\Ticket;

use Illuminate\Http\Request;
use DB;
use Redirect, Input,Session;
use \View;
use App\Ticket;
use App\Wcuser;
use App\Comment;
use App\Pcer;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use EasyWeChat;

class TicketController extends Controller
{
    public function index($openid){

        $wcuser_id = Wcuser::where('openid',$openid)->first()->id;
        $tickets = Ticket::where('wcuser_id',$wcuser_id)
                              ->with('pcer')->orderBy('created_at','DESC')->get();
        return view('Ticket.ticketList',compact('tickets','openid'));
    }


    public function getShow($openid,$id)
    {
        $wcuser_id = Wcuser::where('openid',$openid)->first()->id;
        $ticket = Ticket::where('id',$id)
                              ->with('pcer')->first();
        if ($ticket) {
            if ($wcuser_id==$ticket->wcuser_id) {
                $comments = Comment::where('ticket_id',$id)
                            ->with(['wcuser'=>function($query){
                                $query->with('pcer');
                            }])->get();
                return view('Ticket.ticketData',compact('ticket','comments'));
            } else {
                return view('error');
            }
        } else {
            return view('error');
        }
        
        
        
    }

    public function postEdit()
    {
        $ticket_id = Input::get('ticket_id');
        $validation = Validator::make(Input::all(),[
                'text' => 'required',
            ]);
        if ($validation->fails()) {
         return Redirect::bacn()->withMessage('亲(づ￣3￣)づ╭❤～内容要填写喔！');
        }
            $comment = new Comment;
            $comment->ticket_id = Input::get('ticket_id');
            $comment->from = Input::get('from');
            $comment->wcuser_id = Input::get('wcuser_id');
            $comment->text = Input::get('text');

            $res = $comment->save();

            if ($res) {
                $ticket = Ticket::where('id',$ticket_id)
                                ->with(['pcer'=>function($query){
                                    $query->with('wcuser');
                                }])->first();
                $comments  = Comment::where('wcuser_id',Input::get('wcuser_id'))->where('from',0)->where('created_at','>=',$ticket->updated_at)->get();
                if ($comments->count()==1) {
                    if ($ticket->pcer_id) {
                    /*获取PC队员的openid*/
                      $pcer_openid = $ticket->pcer->wcuser->openid;
                      $notice_pcer = EasyWeChat::notice();
                      $templateId_pcer = 'aCZbEi9-JZbkR4otY8tkeFFV2zwf-lUFKFbos49h1Qc';
                      $url_pcer = "http://pc.nfu.edu.cn/pcertickets/{$pcer_openid}/{$ticket->id}/show";
                      $color_pcer = '#FF0000';
                      $data_pcer = array(
                        "first"    => "机主给你发来消息！",
                        "keynote1" => $comment->text,
                        "keynote2" => "就是现在！",
                        "remark"   => "请尽快处理！么么哒(づ￣ 3￣)づ",
                      );
                      $messageId = $notice_pcer->uses($templateId_pcer)->withUrl($url_pcer)->andData($data_pcer)->andReceiver($pcer_openid)->send();
                    }
                }
                return Redirect::back();
            } else {
                return Redirect::back()->withMessage('网络问题，提交失败，请重新提交(づ￣ 3￣)づ');
            }  
    }

    public function postUpdate()
    {

        $res = Ticket::where('id',Input::get('ticket_id'))
              ->update(['assess'=>Input::get('assess'),'suggestion'=>Input::get('suggestion')]);

        if ($res) {
            return Redirect::back();
        } else {
             return Redirect::back()->withMessage('网络问题，提交失败，请重新提交(づ￣ 3￣)づ');
        }
        
    }

    public function deleteDelticket($openid,$id)
    {
        $openid = Wcuser::where('id',Input::get('wcuser_id'))->first()->openid;
        $res = Ticket::where('id',$id)->delete();
        if ($res) {
            return Redirect::to('mytickets/'.$openid.'/ticketList');
        } else {
            return Redirect::back()->withInput()->withMessage('网络问题，提交失败，请重新提交(づ￣ 3￣)づ');
        }
    }

    public function getCreate($openid,$id)
    {
        $ticket = Ticket::where('id',$id)->first();
        return View::make('Ticket.ticketChange',['ticket'=>$ticket]);
    }

    public function postTicketchange($openid,$id)
    {
        $validation = Validator::make(Input::all(),[
                'name' => 'required',
                'number' => 'required|digits:11',
                'address' => 'required',
                'problem' => 'required',
            ]);
        if ($validation->fails()) {
         return Redirect::back()->withInput(Input::all())->withMessage('亲(づ￣3￣)づ╭❤～内容都要填写喔！检查下手机号码是否写正确了，另外地址要重新核对喔！');
        }

        $name = Input::get('name');
        $number = Input::get('number');
        $shortnum = Input::get('shortnum');
        $area = Input::get('area');
        $address = Input::get('address');
        $date = Input::get('date');
        $hour = Input::get('hour');
        $problem = Input::get('problem');
        $hour1 = Input::get('hour1');
        $date1 = Input::get('date1');   

        $res = Ticket::where('id',$id)->update(['name'=>$name,'number'=>$number,'shortnum'=>$shortnum,'area'=>$area,'address'=>$address,'date'=>$date,'hour'=>$hour,'problem'=>$problem,'date1'=>$date1,'hour1'=>$hour1]);
        if ($res) {
            return Redirect::to('mytickets/'.$openid.'/'.$id.'/show')->withMessage('亲(づ￣3￣)づ╭❤～修改成功');
        } else {
            return Redirect::back()->withMessage('亲(づ￣3￣)づ╭❤～内容都要填写喔！');
        }
        
    }

}
