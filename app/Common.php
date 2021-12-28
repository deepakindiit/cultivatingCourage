<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;
use Illuminate\Support\Facades\Auth;


class Common extends Authenticatable
{
    public static function insertdata($table,$data){
		DB::table($table)->insert($data);
		return DB::getPdo()->lastInsertId();
	}


	public static function getcount($table,$where="",$whereraw=""){
        if($where!=""){
            if($whereraw != "")
                return DB::table($table)->select('*')->where($where)->whereRaw($whereraw)->count();
            else
                return DB::table($table)->select('*')->where($where)->count();
        }else{
            return DB::table($table)->select('*')->count();
        }
    }



    public static function getcountraw($table,$where="",$whereraw){
		if($where!="")
		return DB::table($table)->select('*')->where($where)->where(
            function($q) use($whereraw) {
                $q->whereRaw('NOT FIND_IN_SET('.$whereraw.', deleted_by)')
                ->orwhereRaw('FIND_IN_SET('.$whereraw.', deleted_by) is null');
    }
        )->count();
		else
		return DB::table($table)->select('*')->count();
	}

    public static function getcountofWhereraw($table,$where="",$whereRaw){
		if($where!="")
		return DB::table($table)->select('*')->where($where)->whereRaw($whereRaw)->count();
		else
		return DB::table($table)->select('*')->count();
	}

	public static function updatedata($table,$data,$where){
		return DB::table($table)->where($where)->update($data);
	}

    public static function updatedataraw($table,$data,$where,$whereraw){
		return DB::table($table)->where($where)->where(
            function($q) use($whereraw) {
                $q->whereRaw('NOT FIND_IN_SET('.$whereraw.', deleted_by)')
                ->orwhereRaw('FIND_IN_SET('.$whereraw.', deleted_by) is null');
    }
        )->update($data);
	}

	public static function deletedata($table,$data){
		return DB::table($table)->where($data)->delete();
	}

  

	public static function selectdata($table,$where="",$order="",$offset="",$limit="",$whereraw=""){
        if($where!="" && $order=="")
        $result = DB::table($table)->select('*')->where($where);
        else if(!empty($order) && $where!=""){
            if(!empty($order)){
                if (is_array($order)) {
                    foreach ($order as $key => $value) {
                        $order = $order[$key]; // or $v
                        break;
                    }
                }
                }
        $result = DB::table($table)->select('*')->where($where)->orderby($key,$value);
        }
        else
        $result = DB::table($table)->select('*');
        if($whereraw!=""){
            $result->where(
                function($q) use($whereraw) {
                    $q->whereRaw('NOT FIND_IN_SET('.$whereraw.', deleted_by)')
                    ->orwhereRaw('FIND_IN_SET('.$whereraw.', deleted_by) is null');
                }
            );
        }
        if(is_numeric($offset)){
            $offset = $offset * $limit;
            $result = $result->skip($offset)->take($limit);
        }
        return $result->get();
    }
    public static function selectdataNew($table,$where="",$order="",$offset="",$limit="",$whereraw=""){
        if($where!="" && $order=="")
		$result = DB::table($table)->select('*')->where($where);
		else if(!empty($order) && $where!=""){
			if(!empty($order)){
				if (is_array($order)) {
				    foreach ($order as $key => $value) {
				        $order = $order[$key]; // or $v
				        break;
				    }
				}
				}
		$result = DB::table($table)->select('*')->where($where)->orderby($key,$value);
		}
		else
		$result = DB::table($table)->select('*');
        if($whereraw!=""){
	    	$result->whereRaw($whereraw);
	    }
		if(is_numeric($offset)){
			$offset = $offset * $limit;
			$result = $result->skip($offset)->take($limit);
		}
		return $result->get();
	}
	public static function getfirst($table,$where=""){
		if($where!="")
		return DB::table($table)->select('*')->where($where)->first();
		else
		return DB::table($table)->select('*')->first();
	}

    public static function getfirstraw($table,$where="",$whereraw=""){
		if($where!="")
		return DB::table($table)->select('*')->where($where)->where(
            function($q) use($whereraw) {
            $q->whereRaw('NOT FIND_IN_SET('.$whereraw.', deleted_by)')
            ->orwhereRaw('FIND_IN_SET('.$whereraw.', deleted_by) is null');
        })->first();
		else
		return DB::table($table)->select('*')->first();
	}




}
