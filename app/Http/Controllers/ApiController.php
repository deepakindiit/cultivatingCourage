<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use AppHttpRequestsRegisterAuthRequest;
use TymonJWTAuthExceptionsJWTException;
use JWTAuth,Session;
use Validator;
use IlluminateHttpRequest;
// use App\Traits\ImagesTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Mail, Hash, Auth;
use App\Common;
use App\Models\VideoCategory;
use App\Models\UserVideoCourse;
use App\Models\UserVideoCourseAdditionalInformation;
use App\Models\UserRecipie;
use App\Models\UserPodcast;
use App\Models\CommonPage;
use App\Models\Newsletter;
use App\Models\FavouriteItem;
use DB;



class ApiController extends Controller
{
    public function seller_registration(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make(
            $request->all(),
            [
                'name'          => 'required',
                'password'      => 'required',
                'email'         => 'required|email'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 200);
        }

        $check_email_exists = User::where('email', $input['email'])->first();
        
        if ($check_email_exists) {
            return response()->json(['status' => false,'message' => 'This Email is already exists', 'code' => 400]);
        }
        
        User::create([
                    'name'              => $input['name'],
                    'email'             => $input['email'],
                    // 'decrypt_password'  => $input['password'],
                    'password'          => Hash::make($input['password'])
                ]);

        return response()->json(['status' => true,'code'=>200,'message' => 'User registration successfully']);
    }

    public function user_login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $input = $request->all();
        // print_r($credentials); die();
        $validator = Validator::make(
            $request->all(),
            [
                'email'      => 'required|email',
                'password'   => 'required'
            ]
        );
        if ($validator->fails()) {
            $response['code'] = 404;
            $response['status'] = $validator->errors()->first();
            $response['message'] = "missing parameters";
            return response()->json($response);
        }
        
        $checkDataEmail = User::where('email',$input['email'])
                            ->first();

        if((Hash::check($request->password, $checkDataEmail->password))){
            // $user = auth()->userOrFail();
            return response()->json(['status' => true,'message' => 'User login Successfuly','data' => $checkDataEmail,'code' => 200]);
        } else {
            return response()->json(['status' => false,'message' => 'Password did not match', 'code' => 400]);
        }
    }

    public function user_forgot_password(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email'      => 'required|email',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 200);
        }

        $check_email_exists = User::where('email', $request['email'])->first();

        if (empty($check_email_exists)) {
            return response()->json(['status' => false,'message' => 'Email not exists.','code' => 400]);
        }

      
        $check_email_exists->otp = $this->generateRandomString(6);
       
        if ($check_email_exists->save()) {
            $project_name = env('App_name');
            $email        = $request['email'];
            
            $otp  = $check_email_exists->otp;
            // send email confirmation link to user's email

            $replace_with = ['name'=>$check_email_exists['name'],'email'=>$check_email_exists['email'],'otp'=>$otp];      
            try {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                    Mail::send('frontend.emails.user_forgot_password_api', ['data' => $replace_with], function ($message) use ($email, $project_name) {
                        $message->to($email, $project_name)->subject('User Forgot Password');
                    });
                }
            } catch (Exception $e) {
            }

            return response()->json(['status' => true,'email'=>$check_email_exists['email'], 'message' => 'Email sent on registered Email-id.','code' => 200], Response::HTTP_OK);
        } else {
            return response()->json(['status' => false, 'message' => 'Something went wrong, Please try again later.','code' => 400]);
        }
    }

    public function resetPassword(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make(
            $request->all(),
            [
                'otp'           => 'required',
                'email'           => 'required',
                'password'      => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 200);
        }
        // print_r($input); die();
        $check_otp_exists = User::where('otp', $input['otp'])
                                    ->where('email', $input['email'])
                                    ->first();
        
        if ($check_otp_exists) {
            User::where('otp', $input['otp'])
                  ->where('email', $input['email'])
                  ->update([
                            // 'decrypt_password'  => $input['password'],
                            'password'          => Hash::make($input['password'])
                    ]);

            return response()->json(['status' => true,'code'=>200,'message' => 'Password reset successfully']);
        }else{
            return response()->json(['status' => false, 'message' => 'Otp does not match.','code' => 400]);
        }
    }


    private function generateRandomString($length) {
        $characters = '123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return strtoupper($randomString);
    }

    public function getVideoCourseData(Request $request){

        $input = $request->all();

        $offset =4;
        
        $data['result']  = UserVideoCourse::select('*')
                              ->with('videoCategory','videoCourseWorkingDetail','videoCourseAdditionalInformation')
                              ->with('videoCategory')
                              ->orderby('id','desc')
                              // ->where('user_id',$input['id'])
                              ->take(4)
                              ->get();
       
        foreach ( $data['result'] as $key => $value) {
             
            $check_fav = FavouriteItem::where('type_id',$value['id'])
                                         ->where('type','home')
                                         ->where('user_id',$input['id'])
                                         ->first();
            if(empty($check_fav)){
                $value->is_fav = 0; 
            }else{
                $value->is_fav = 1; 
            }
        }
                              
        $result1 =  UserVideoCourse::select('*')
                              ->with('videoCategory','videoCourseWorkingDetail','videoCourseAdditionalInformation')
                          ->select('*')
                          // ->where('user_id',$input['id'])
                          ->orderby('id' ,'desc')
                          ->skip(4)
                          ->take(4)
                          ->get();   

        $data['offset'] = 4;
        // $data['offset'] = $offset+8;

        $data['next'] = count($result1);
        return response()->json(['status' => true,'code'=>200,'data'=>$data,'message' => 'Get video course data list']);
    }
    
    public function load_moreVideos(Request $request){
        $input = $request->all();
        $offset = $request->offset;
        
        $data['result']  =  UserVideoCourse::select('*')
                              ->with('videoCategory','videoCourseWorkingDetail','videoCourseAdditionalInformation')
                              ->select('*')
                              // ->where('user_id',$input['id'])
                              ->orderby('id','desc')
                              ->skip($offset)
                              ->take(4)
                              ->get();

        foreach ( $data['result'] as $key => $value) {
             
            $check_fav = FavouriteItem::where('type_id',$value['id'])
                                         ->where('type','home')
                                         ->where('user_id',$input['id'])
                                         ->first();
            if(empty($check_fav)){
                $value->is_fav = 0; 
            }else{
                $value->is_fav = 1; 
            }
        }

        $result22  =  UserVideoCourse::select('*')
                          ->with('videoCategory','videoCourseWorkingDetail','videoCourseAdditionalInformation')
                          ->select('*')
                          // ->where('user_id',$input['id'])
                          ->orderby('id' , 'desc')
                          ->skip($offset+4)
                          ->take(4)
                          ->get();

        $data['offset'] = $offset+4;
        
        $data['next'] = count($result22);

        return response()->json(['status' => true,'code'=>200,'data'=>$data,'message' => 'Get video course data list']);
    }

    public static function getfilterdata($skip){
      $offset1 = $skip * 8;
      $result = DB::table('movies')
            ->select('*')
            ->where('status','=','1')
            ->orderby('id' , 'desc')
            ->skip($offset1)
            ->take(8)->get();  
    }

    public function videoCourseDetail(Request $request,$courseId){
        // dd($courseId,$request->user_id);                        
        $data = UserVideoCourse::with('videoCategory','videoCourseWorkingDetail','videoCourseAdditionalInformation')->where('id',$courseId)->first();

        $check_fav = FavouriteItem::where('type_id',$courseId)
                                ->where('type','home')
                                ->where('user_id',$request->user_id)
                                ->first();
        if(empty($check_fav)){
            $data->is_fav = 0; 
        }else{
           $data->is_fav = 1; 
        }
                           
        return response()->json(['status' => true,'code'=>200,'data'=>$data,'message' => 'Video course details']);
    }

    public function getRecipieData(Request $request){
        $data = UserRecipie::with('recipieIngredients')->get();
        
        foreach ($data as $key => $value) {
            
            $check_fav = FavouriteItem::where('type_id',$value['id'])
                                        ->where('type','recipie')
                                        ->where('user_id',$request->user_id)
                                        ->first();
            if(empty($check_fav)){
                $value->is_fav = 0; 
            }else{
               $value->is_fav = 1; 
            }
        }

        // dd($data);                     
        return response()->json(['status' => true,'code'=>200,'data'=>$data,'message' => 'Get User recipie list']);
    }

    public function recipieDetail(Request $request,$recipieId){
       
        $input          = $request->all();

        $data1 = UserRecipie::with('recipieIngredients')
                            ->where('id',$recipieId)
                            ->first();

        $check_fav = FavouriteItem::where('type_id',$recipieId)
                                    ->where('type','recipie')
                                    ->where('user_id',$request->user_id)
                                    ->first();
        if(empty($check_fav)){
            $data1->is_fav = 0; 
        }else{
           $data1->is_fav = 1; 
        }
                            
        return response()->json(['status' => true,'code'=>200,'data'=>$data1,'message' => 'Recipie details']);
    }

    public function getUserPodcastData(Request $request){

        $input  = $request->all();

        $offset = 7;
    
        $data['result']  = UserPodcast::select('*')
                              ->orderby('id','desc')
                              // ->where('user_id',$input['id'])
                              ->take(7)
                              ->get();
       
         
        foreach ( $data['result'] as $key => $value) {
             
            $check_fav = FavouriteItem::where('type_id',$value['id'])
                                         ->where('type','podcast')
                                         ->where('user_id',$input['id'])
                                         ->first();
            if(empty($check_fav)){
                $value->is_fav = 0; 
            }else{
                $value->is_fav = 1; 
            }
        }
                              
        $result1 =  UserPodcast::select('*')
                          ->select('*')
                          // ->where('user_id',$input['id'])
                          ->orderby('id' ,'desc')
                          ->skip(7)
                          ->take(7)
                          ->get();   

        $data['offset'] = 7;
        // $data['offset'] = $offset+8;

        $data['next'] = count($result1);
        return response()->json(['status' => true,'code'=>200,'data'=>$data,'message' => 'Get User podcast list']);
    }
    
    public function podcast_load_moreVideos(Request $request){
        $input = $request->all();
        $offset = $request->offset;
                           
        $data['result']  =  UserPodcast::select('*')
                              ->select('*')
                              // ->where('user_id',$input['id'])
                              ->orderby('id','desc')
                              ->skip($offset)
                              ->take(7)
                              ->get();

        foreach ( $data['result'] as $key => $value) {
             
            $check_fav = FavouriteItem::where('type_id',$value['id'])
                                         ->where('type','podcast')
                                         ->where('user_id',$input['id'])
                                         ->first();
            if(empty($check_fav)){
                $value->is_fav = 0; 
            }else{
                $value->is_fav = 1; 
            }
        }
                              
        $result22  =  UserPodcast::select('*')
                          ->select('*')
                          // ->where('user_id',$input['id'])
                          ->orderby('id' , 'desc')
                          ->skip($offset+7)
                          ->take(7)
                          ->get();

        $data['offset'] = $offset+7;
        
        $data['next'] = count($result22);

        return response()->json(['status' => true,'code'=>200,'data'=>$data,'message' => 'Get User recipie list']);
    }

    public function userPodcastDetail(Request $request,$podcastId){
        $input = $request->all();

        $data = UserPodcast::where('id',$podcastId)->first();

        $check_fav = FavouriteItem::where('type_id',$podcastId)
                                ->where('type','podcast')
                                ->where('user_id',$request->user_id)
                                ->first();

        if(empty($check_fav)){
            $data->is_fav = 0; 
        }else{
           $data->is_fav = 1; 
        }
       
        return response()->json(['status' => true,'code'=>200,'data'=>$data,'message' => 'User Podcast details']);
    }

    public function getCommonPageData(Request $request,$type){

        $input = $request->all();
        $offset =7;
        $data['result']  = CommonPage::where('type',$type)
                                      // ->where('user_id',$input['id'])
                                      ->select('*')
                                      ->orderby('id','desc')
                                      ->take(7)
                                      ->get();

        $result1 =  CommonPage::where('type',$type)
                              // ->where('user_id',$input['id'])
                              ->select('*')
                              ->orderby('id' ,'desc')
                              ->skip(7)
                              ->take(7)
                              ->get();   

        $data['offset'] = 7;
        // $data['offset'] = $offset+8;
        $data['next'] = count($result1);
        return response()->json(['status' => true,'code'=>200,'data'=>$data,'message' => 'Get '.$type.' list']);
    }
    
    public function getCommonPageLoadMoreVideos(Request $request,$type){
        $input = $request->all();
        $offset = $request->offset;
        
        $data['result'] =   CommonPage::where('type',$type)
                              // ->where('user_id',$input['id'])
                              ->select('*')
                              ->orderby('id','desc')
                              ->skip($offset)
                              ->take(7)
                              ->get();

        $result22       =   CommonPage::where('type',$type)
                              // ->where('user_id',$input['id'])
                              ->select('*')
                              ->orderby('id' , 'desc')
                              ->skip($offset+7)
                              ->take(7)
                              ->get();

        $data['offset'] = $offset+7;
        
        $data['next'] = count($result22);

        return response()->json(['status' => true,'code'=>200,'data'=>$data,'message' => 'Get '.$type.' list','type'=>$type]);
    }

    // public function commonPageDetail(Request $request,$id,$type){
    //     if($type=='term_and_condtion'||$type=='privacy_policy'){
    //         $data = CommonPage::where('type',$type)
    //                             ->where('user_id',$id)
    //                             ->first();
    //     }else{
    //         $data = CommonPage::where('type',$type)
    //                             ->where('id',$id)
    //                             ->first();
    //     }
    //     return response()->json(['status' => true,'code'=>200,'data'=>$data,'message' => 'Get '.$type.' detail']);
    // }

    // public function getCommonPageData(Request $request,$type){
    //     $data = CommonPage::where('type',$type)->get();
    //     return response()->json(['status' => true,'code'=>200,'data'=>$data,'message' => 'Get '.$type.' list']);
    // }

    public function commonPageDetail(Request $request,$id,$type){
        if($type=='term_and_condtion'||$type=='privacy_policy'){
            $data = CommonPage::where('type',$type)
                                // ->where('user_id',$id)
                                ->first();
        }else{
            $data = CommonPage::where('type',$type)
                                ->where('id',$id)
                                ->first();
        }
        return response()->json(['status' => true,'code'=>200,'data'=>$data,'message' => 'Get '.$type.' detail']);
    }

    public function newsletter(Request $request){

        $input = $request->all();
        $validator = Validator::make(
            $request->all(),
            [
                'email'         => 'required|email'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 200);
        }

        $check_email_exists = Newsletter::where('email', $input['email'])->first();
        if($check_email_exists){
            return response()->json(['status' => false,'code'=>400,'message' => 'This email is already subscribed']);
        }else{
                Newsletter::create([
                    'user_id'    => $input['user_id'],
                    'email'      => $input['email'],
                ]);

            return response()->json(['status' => true,'code'=>200,'message' => 'User registration successfully']);
        }
    }

    public function profileUpdate(Request $request){
        $input = $request->all();
        // dd($input);
        if(isset($input['password']) && isset($input['new_password'])){
            $userData = User::where('id',$input['user_id'])
                                        ->update([
                                            'password'      => @$input['new_password']
                                        ]);
        }else{
            $userData = User::where('id',$input['user_id'])
                                        ->update([
                                            'name'      => @$input['name'],
                                            'email'     => @$input['email'],
                                        ]);
        }

        return response()->json(['status' => true,'data'=>$userData,'code'=>200,'message' => 'User profile updated']);
    }

    public function getProfile(Request $request,$id){

        $userDetail = User::where('id',$id)
                            ->first();

        return response()->json(['status' => true,'data'=>$userDetail,'code'=>200,'message' => 'User profile details']);
    }
    

     public function favouriteItemList(Request $request,$type){
        $input          = $request->all();
               
        $data =  FavouriteItem::where('type',$type)
                             ->where('user_id',$input['id'])
                             ->select('*')
                             ->orderby('id','desc')
                             ->get();
        // dd($favouriteItemList);
                             
       return response()->json(['status' => true,'code'=>200,'data'=>$data,'message' => 'Get '.$type.' detail']);     
    }


    public function favouriteItemAdded(Request $request,$type){
        $input          = $request->all();

        $checkItemExist =  FavouriteItem::where('type',$type)
                                 ->where('user_id',$input['id'])
                                 ->where('type_id',$input['type_id'])
                                 ->select('*')
                                 ->orderby('id','desc')
                                 ->first();

        if($checkItemExist){
            $userDetail = FavouriteItem::where('type',$type)
                                 ->where('user_id',$input['id'])
                                 ->where('type_id',$input['type_id'])
                                 ->delete();

            return response()->json(['status' => false,'data'=>$userDetail,'code'=>400,'message' => 'Recipie removed as favourite']);
        }else{
            $userDetail =FavouriteItem::create([
                                        'favourite_item_status' =>'true',
                                        'user_id'               =>$input['id'],
                                        'type'                  =>$type ,
                                        'type_id'               =>$input['type_id']
                                    ]);

        return response()->json(['status' => true,'data'=>$userDetail,'code'=>200,'message' => 'Recipie added as favourite']);    
        }
    }

    public function favouriteItemData(Request $request){
        
        $input          = $request->all();
        
        $data =  FavouriteItem::with('CommonPage')
                                         ->where('type','essential_oil')
                                         ->where('user_id',$input['id'])
                                         ->select('*')
                                         ->orderby('id','desc')
                                         ->get()
                                         ->toArray();
        // dd($data);                                 
        return response()->json(['status' => true,'data'=>$data,'code'=>200,'message' => 'Get favourite items data']);    
    }


    public function favouriteItemListData(Request $request,$type){
        $input          = $request->all();
      
        $offset = $request->offset;
        switch ($type) {
            case 'essential_oil':
                $data['essential_oil_favourite_list'] =  FavouriteItem::with('CommonPage')
                                                 ->where('type','essential_oil')
                                                 ->where('user_id',$input['id'])
                                                 ->select('*')
                                                 ->orderby('id','desc')
                                                 // ->take(7)
                                                 ->skip($offset)
                                                 ->take(7)
                                                 ->get();

                $result1 =  FavouriteItem::with('CommonPage')
                                     ->where('type','essential_oil')
                                     ->where('user_id',$input['id'])
                                     ->select('*')
                                     ->orderby('id','desc')
                                     // ->take(7)
                                     ->skip($offset+7)
                                     ->take(7)
                                     ->get();   

                $data['offset'] = $offset+7;
                $data['next'] = count($result1);
                                                 
                break;
            case 'motivation':
                $data['motivation_favourite_list'] =  FavouriteItem::with('CommonPage')
                                                 ->where('type','motivation')
                                                 ->where('user_id',$input['id'])
                                                 ->select('*')
                                                 ->orderby('id','desc')
                                                 ->skip($offset)
                                                 ->take(7)
                                                 ->get();

                $result1 =  FavouriteItem::with('CommonPage')
                                     ->where('type','essential_oil')
                                     ->where('user_id',$input['id'])
                                     ->select('*')
                                     ->orderby('id','desc')
                                     // ->take(7)
                                     ->skip($offset+7)
                                     ->take(7)
                                     ->get();   

                $data['offset'] = $offset+7;
                $data['next'] = count($result1);
                                                 
                break;
            case 'cultivating_courage':
                $data['cultivating_Courage_favourite_list'] =  FavouriteItem::with('CommonPage')
                                                 ->where('type','cultivating_courage')
                                                 ->where('user_id',$input['id'])
                                                 ->select('*')
                                                 ->orderby('id','desc')
                                                 ->skip($offset)
                                                 ->take(7)
                                                 ->get();  

                $result1 =  FavouriteItem::with('CommonPage')
                                     ->where('type','cultivating_courage')
                                     ->where('user_id',$input['id'])
                                     ->select('*')
                                     ->orderby('id','desc')
                                     ->skip($offset+7)
                                     ->take(7)
                                     ->get();   

                $data['offset'] = $offset+7;
                $data['next'] = count($result1);
                                                 
                break;
            case 'ag_news':
                $data['agNews_favourite_list'] =  FavouriteItem::with('CommonPage')
                                               ->where('type','ag_news')
                                               ->where('user_id',$input['id'])
                                               ->select('*')
                                               ->orderby('id','desc')
                                               ->skip($offset)
                                               ->take(7)
                                               ->get(); 

                $result1 =  FavouriteItem::with('CommonPage')
                                     ->where('type','ag_news')
                                     ->where('user_id',$input['id'])
                                     ->select('*')
                                     ->orderby('id','desc')
                                     ->skip($offset+7)
                                     ->take(7)
                                     ->get();   

                $data['offset'] = $offset+7;
                $data['next'] = count($result1);

                break;
            case 'recipie':

                $data['recipie_favourite_list'] =  FavouriteItem::where('favourite_items.user_id',$input['id'])                  
                              ->leftJoin('user_recipies','favourite_items.type_id','=','user_recipies.id')
                              ->where('type','recipie')
                              ->select('favourite_items.*','user_recipies.recipie_short_description','user_recipies.thumbnail_image','user_recipies.recipie_title','user_recipies.recipie_intro','user_recipies.description','.cooking_detail')
                              ->orderby('id','desc')
                              ->skip($offset)
                              ->take(7)
                              ->get();  
                              
                $result1 =  FavouriteItem::where('favourite_items.user_id',$input['id'])                  ->leftJoin('user_recipies','favourite_items.type_id','=','user_recipies.id')
                              ->where('type','recipie')
                              ->select('favourite_items.*','user_recipies.recipie_short_description','user_recipies.thumbnail_image','user_recipies.recipie_title','user_recipies.recipie_intro','user_recipies.description','.cooking_detail')
                             ->orderby('id','desc')
                             ->skip($offset+7)
                             ->take(7)
                             ->get();   

                $data['offset'] = $offset+7;
                $data['next'] = count($result1);                                        

                break;
            case 'podcast':
                $data['podcast_favourite_list'] =  FavouriteItem::where('favourite_items.user_id',$input['id'])                 
                         ->leftJoin('user_podcasts','favourite_items.type_id','=','user_podcasts.id')
                         ->where('type','recipie')
                         ->select('favourite_items.*','user_podcasts.title','user_podcasts.thumbnail_image')
                         ->orderby('id','desc')
                          ->skip($offset)
                          ->take(7)
                         ->get();
        
                $result1 =  FavouriteItem::where('favourite_items.user_id',$input['id'])                 
                         ->leftJoin('user_podcasts','favourite_items.type_id','=','user_podcasts.id')
                         ->where('type','recipie')
                         ->select('favourite_items.*','user_podcasts.title','user_podcasts.thumbnail_image')
                         ->orderby('id','desc')
                         ->skip($offset+7)
                         ->take(7)
                         ->get();   

                $data['offset'] = $offset+7;
                $data['next'] = count($result1);

                break;
            case 'home':
                $data['home_video_favourite_list'] =  FavouriteItem::where('favourite_items.user_id',$input['id'])                  
                ->leftJoin('user_video_courses','favourite_items.type_id','=','user_video_courses.id')
                ->leftJoin('video_categories','user_video_courses.video_category_id','=','video_categories.id')
                ->leftJoin('user_video_course_additional_informations','favourite_items.type_id','=','user_video_course_additional_informations.id')
                ->where('type','home')
                ->select('favourite_items.*','user_video_courses.thumbnail_image','user_video_courses.video_title','user_video_courses.video_description','user_video_courses.workout_detail','video_categories.category','user_video_courses.video_category_id','user_video_course_additional_informations.additional_information_title','user_video_course_additional_informations.additional_information_detail')
                ->orderby('id','desc')
                ->skip($offset)
                ->take(7)
                ->get();

                $result1 =  FavouriteItem::where('favourite_items.user_id',$input['id'])                  
                        ->leftJoin('user_video_courses','favourite_items.type_id','=','user_video_courses.id')
                        ->leftJoin('video_categories','user_video_courses.video_category_id','=','video_categories.id')
                        ->leftJoin('user_video_course_additional_informations','favourite_items.type_id','=','user_video_course_additional_informations.id')
                        ->where('type','home')
                        ->select('favourite_items.*','user_video_courses.thumbnail_image','user_video_courses.video_title','user_video_courses.video_description','user_video_courses.workout_detail','video_categories.category','user_video_courses.video_category_id','user_video_course_additional_informations.additional_information_title','user_video_course_additional_informations.additional_information_detail')
                        ->orderby('id','desc')
                        ->skip($offset+7)
                        ->take(7)
                        ->get();   

                $data['offset'] = $offset+7;
                $data['next'] = count($result1);            
                    
                    break;

        default:
            $data['farmViews_favourite_list'] =  FavouriteItem::with('CommonPage')
                                         ->where('type','farm_wife_view')
                                         ->where('user_id',$input['id'])
                                         ->select('*')
                                         ->orderby('id','desc')
                                          ->skip($offset)
                                          ->take(7)
                                         ->get(); 
        
            $result1 =  FavouriteItem::with('CommonPage')
                                         ->where('type','farm_wife_view')
                                         ->where('user_id',$input['id'])
                                         ->select('*')
                                         ->orderby('id','desc')
                                         ->skip($offset+7)
                                         ->take(7)
                                         ->get();   

             $data['offset'] = $offset+7;
            $data['next'] = count($result1);

            break;
        }                  
                                                                                                                                                                                                                                                                       
        return response()->json(['status' => true,'data'=>$data,'code'=>200,'message' => 'Get favourite items detail']);    
    }

    public function favouriteItemLoadMoreVideos(Request $request){
        $input = $request->all();
        $type   = $input['tabType'];    
        $offset = $request->offset;
        
        switch ($type) {
            case 'essential_oil':
                $data['result'] =  FavouriteItem::with('CommonPage')
                                                 ->where('type',$type)
                                                 ->where('user_id',$input['id'])
                                                 ->select('*')
                                                 ->orderby('id','desc')
                                                 ->skip($offset)
                                                 ->take(7)
                                                 ->get(); 

                $result22 =  FavouriteItem::with('CommonPage')
                                     ->where('type',$type)
                                     ->where('user_id',$input['id'])
                                     ->select('*')
                                     ->orderby('id','desc')
                                     ->skip($offset+7)
                                     ->take(7)
                                     ->get();   
                                                
                $data['offset'] = $offset+7;
                $data['next'] = count($result22);

                break;
            case 'motivation':
                $data['result'] =  FavouriteItem::with('CommonPage')
                                                 ->where('type',$type)
                                                 ->where('user_id',$input['id'])
                                                 ->select('*')
                                                 ->orderby('id','desc')
                                                 ->skip($offset)
                                                 ->take(7)
                                                 ->get(); 

                $result22 =  FavouriteItem::with('CommonPage')
                                     ->where('type',$type)
                                     ->where('user_id',$input['id'])
                                     ->select('*')
                                     ->orderby('id','desc')
                                     ->skip($offset+7)
                                     ->take(7)
                                     ->get();   
                                                
                $data['offset'] = $offset+7;
                $data['next'] = count($result22);

                break;
            case 'cultivating_courage':
                 $data['result'] =  FavouriteItem::with('CommonPage')
                                                 ->where('type',$type)
                                                 ->where('user_id',$input['id'])
                                                 ->select('*')
                                                 ->orderby('id','desc')
                                                 ->skip($offset)
                                                 ->take(7)
                                                 ->get(); 

                $result22 =  FavouriteItem::with('CommonPage')
                                     ->where('type',$type)
                                     ->where('user_id',$input['id'])
                                     ->select('*')
                                     ->orderby('id','desc')
                                     ->skip($offset+7)
                                     ->take(7)
                                     ->get();   

                $data['offset'] = $offset+7;
                $data['next'] = count($result22);
                                                 
                break;
            case 'ag_news':
                $data['result'] =  FavouriteItem::with('CommonPage')
                                               ->where('type',$type)
                                               ->where('user_id',$input['id'])
                                               ->select('*')
                                               ->orderby('id','desc')
                                               ->skip($offset)
                                               ->take(7)
                                               ->get(); 

                $result22 =  FavouriteItem::with('CommonPage')
                                     ->where('type',$type)
                                     ->where('user_id',$input['id'])
                                     ->select('*')
                                     ->orderby('id','desc')
                                     ->skip($offset+7)
                                     ->take(7)
                                     ->get();   

                $data['offset'] = $offset+7;
                $data['next'] = count($result22);  

                break;
            case 'recipie':

                $data['result']=  FavouriteItem::where('favourite_items.user_id',$input['id'])                  ->leftJoin('user_recipies','favourite_items.type_id','=','user_recipies.id')
                              ->where('type',$type)
                              ->select('favourite_items.*','user_recipies.recipie_short_description','user_recipies.thumbnail_image','user_recipies.recipie_title','user_recipies.recipie_intro','user_recipies.description','.cooking_detail')
                              ->orderby('id','desc')
                              ->skip($offset)
                              ->take(7)
                              ->get();  

                $result22 =  FavouriteItem::where('favourite_items.user_id',$input['id'])                  ->leftJoin('user_recipies','favourite_items.type_id','=','user_recipies.id')
                              ->where('type',$type)
                              ->select('favourite_items.*','user_recipies.recipie_short_description','user_recipies.thumbnail_image','user_recipies.recipie_title','user_recipies.recipie_intro','user_recipies.description','.cooking_detail')
                              ->skip($offset+7)
                              ->take(7)
                              ->get(); 
                                                
                $data['offset'] = $offset+7;
                $data['next'] = count($result22);                                      
            
                break;
            case 'podcast':
                $data['result'] =  FavouriteItem::where('favourite_items.user_id',$input['id'])                 
                         ->leftJoin('user_podcasts','favourite_items.type_id','=','user_podcasts.id')
                         ->where('type',$type)
                         ->select('favourite_items.*','user_podcasts.title','user_podcasts.thumbnail_image')
                         ->orderby('id','desc')
                         ->skip($offset)
                         ->take(7)
                         ->get(); 
        
                $result22 =  FavouriteItem::where('favourite_items.user_id',$input['id'])                 
                         ->leftJoin('user_podcasts','favourite_items.type_id','=','user_podcasts.id')
                         ->where('type',$type)
                         ->select('favourite_items.*','user_podcasts.title','user_podcasts.thumbnail_image')
                         ->orderby('id','desc')
                         ->skip($offset+7)
                         ->take(7)
                         ->get();   
                                                
                $data['offset'] = $offset+7;
                $data['next'] = count($result22);

                break;
            case 'home':
                    $data['result'] =  FavouriteItem::where('favourite_items.user_id',$input['id'])                  
                                ->leftJoin('user_video_courses','favourite_items.type_id','=','user_video_courses.id')
                                ->leftJoin('video_categories','user_video_courses.video_category_id','=','video_categories.id')
                                ->leftJoin('user_video_course_additional_informations','favourite_items.type_id','=','user_video_course_additional_informations.id')
                                ->where('type',$type)
                                ->select('favourite_items.*','user_video_courses.thumbnail_image','user_video_courses.video_title','user_video_courses.video_description','user_video_courses.workout_detail','video_categories.category','user_video_courses.video_category_id','user_video_course_additional_informations.additional_information_title','user_video_course_additional_informations.additional_information_detail')
                                ->orderby('id','desc')
                                ->skip($offset)
                                ->take(7)
                                ->get();

                $result22       =   FavouriteItem::where('favourite_items.user_id',$input['id'])
                                        ->leftJoin('user_video_courses','favourite_items.type_id','=','user_video_courses.id')
                                        ->leftJoin('video_categories','user_video_courses.video_category_id','=','video_categories.id')
                                        ->leftJoin('user_video_course_additional_informations','favourite_items.type_id','=','user_video_course_additional_informations.id')
                                        ->where('type',$type)
                                        ->select('favourite_items.*','user_video_courses.thumbnail_image','user_video_courses.video_title','user_video_courses.video_description','user_video_courses.workout_detail','video_categories.category','user_video_courses.video_category_id','user_video_course_additional_informations.additional_information_title','user_video_course_additional_informations.additional_information_detail')
                                        ->skip($offset+7)
                                        ->take(7)
                                        ->get();
                                                
 
                    $data['offset'] = $offset+7;
                    $data['next'] = count($result22); 
                break;

        default:
            $data['result'] =  FavouriteItem::with('CommonPage')
                                         ->where('type',$type)
                                         ->where('user_id',$input['id'])
                                         ->select('*')
                                         ->orderby('id','desc')
                                         ->skip($offset)
                                         ->take(7)
                                         ->get(); 

            $result22       =   FavouriteItem::with('CommonPage')
                                  ->where('type',$type)
                                  ->where('user_id',$input['id'])
                                  ->select('*')
                                  ->orderby('id','desc')
                                  ->skip($offset+7)
                                  ->take(7)
                                  ->get();
                                            
            $data['offset'] = $offset+7;
            $data['next']   = count($result22);                                

            break;
        }

        return response()->json(['status' => true,'code'=>200,'data'=>$data,'message' => 'Get '.$type.' list']);
    }

    public function changePushNotificationStatus(Request $request){
        $input = $request->all();     
        // dd($input['push_notification_status']);
        if($input['push_notification_status']=="active"){
            User::where('id',$input['user_id'])
                            ->update([
                                'push_notification_status'=>$input['push_notification_status']
                             ]);
            return response()->json(['status' => true,'code'=>200,'message' => 'Push notification status changed to active']);    
        }else{
            User::where('id',$input['user_id'])
                            ->update([
                                'push_notification_status'=>$input['push_notification_status']
                             ]);
            return response()->json(['status' => false,'code'=>400,'message' => 'Push notification status changed to inactive']);    
        }

    }

}









