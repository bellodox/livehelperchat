<?php

class erLhcoreClassUserDep{

   function __construct()
   {

   }

   public static function getUserDepartaments($userID = false)
   {

         if (isset($GLOBALS['lhCacheUserDepartaments_'.$userID])) return $GLOBALS['lhCacheUserDepartaments_'.$userID];
         if (isset($_SESSION['lhCacheUserDepartaments_'.$userID])) return $_SESSION['lhCacheUserDepartaments_'.$userID];


         $db = ezcDbInstance::get();

         if ($userID === false)
         {
             $currentUser = erLhcoreClassUser::instance();
             $userID = $currentUser->getUserID();
         }

         $stmt = $db->prepare('SELECT lh_userdep.dep_id FROM lh_userdep WHERE user_id = :user_id ORDER BY id ASC');
         $stmt->bindValue( ':user_id',$userID);

         $stmt->execute();

         $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

         $idArray = array();

         foreach ($rows as $row)
         {
             $idArray[] = $row['dep_id'];
         }

         $GLOBALS['lhCacheUserDepartaments_'.$userID] = $idArray;
         $_SESSION['lhCacheUserDepartaments_'.$userID] = $idArray;

         return $idArray;
   }

   public static function getDefaultUserDepartment($userID = false) {
   		return array_shift(self::getUserDepartaments($userID = false));
   }

   public static function addUserDepartaments($Departaments, $userID = false,$UserData = false)
   {
       $db = ezcDbInstance::get();
       if ($userID === false)
       {
           $currentUser = erLhcoreClassUser::instance();
           $userID = $currentUser->getUserID();
       }

       $stmt = $db->prepare('DELETE FROM lh_userdep WHERE user_id = :user_id');
       $stmt->bindValue( ':user_id',$userID);
       $stmt->execute();

       foreach ($Departaments as $DepartamentID)
       {
            $stmt = $db->prepare('INSERT INTO lh_userdep (user_id,dep_id,hide_online,last_activity) VALUES (:user_id,:dep_id,:hide_online,0)');
            $stmt->bindValue( ':user_id',$userID,PDO::PARAM_INT);
            $stmt->bindValue( ':dep_id',$DepartamentID,PDO::PARAM_INT);
            $stmt->bindValue( ':hide_online',$UserData->hide_online,PDO::PARAM_INT);
            $stmt->execute();
       }

       if (isset($_SESSION['lhCacheUserDepartaments_'.$userID])){
           unset($_SESSION['lhCacheUserDepartaments_'.$userID]);
       }

   }

   public static function setHideOnlineStatus($UserData) {
       $db = ezcDbInstance::get();
       $stmt = $db->prepare('UPDATE lh_userdep SET hide_online = :hide_online WHERE user_id = :user_id');
       $stmt->bindValue( ':hide_online',$UserData->hide_online);
       $stmt->bindValue( ':user_id',$UserData->id);
       $stmt->execute();
   }

   public static function getSession()
   {
        if ( !isset( self::$persistentSession ) )
        {
            self::$persistentSession = new ezcPersistentSession(
                ezcDbInstance::get(),
                new ezcPersistentCodeManager( './pos/lhdepartament' )
            );
        }
        return self::$persistentSession;
   }

   private static $persistentSession;

}


?>