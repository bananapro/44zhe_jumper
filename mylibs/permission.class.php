<?php

class permission{

        public static $PERMISSIONS;
        public static $P_GROUP;
        var $roles2permission;

        function permission(){

				if(!self::$PERMISSIONS){
					require MYCONFIGS."permissions.php";
					self::$PERMISSIONS = $PERMISSIONS;
					self::$P_GROUP = $P_GROUP;
				}

                $this->roles2permission = new Roles2permission();
        }

        /**
        * @group_id 分组权限ID
        * @permission  表单提交的权限序列
        */

        function createAccessmask($group_id, $permission){

                $permission = array_flip($permission);

                $accessmask = str_repeat('0', count(self::$PERMISSIONS[$group_id]));
                foreach (self::$PERMISSIONS[$group_id] as $k => $v)
                {
                        $flag = isset($permission[$k])?'1':'0';
                        $accessmask[$k] = $flag;
                }
                return $accessmask;
        }


        function mask2array($mask){

                $mask_length = strlen($mask);
                for ($j=0;$j<$mask_length;$j++) ($mask[$j])?$array[$j] = $mask[$j]:'';
                return $array;
        }


        function getAcessmask($role_id, $group_id){

                $mask = $this->roles2permission->getAccessmask($role_id, $group_id);
                return $mask;
        }


        function checkPermission($mask, $permission_id){

                return (bool)@$mask[$permission_id];
        }

        function checkGroupPermission($role_id, $group_id){

                return $this->roles2permission->getGroupPermission($role_id, $group_id);
        }
}

?>