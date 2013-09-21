<?php if (!defined('APPLICATION')) exit();
/**
 *  'Alias' plugin for Vanilla Forums.
 *
 *  Show list of prior usernames in the profile of each user who has changed his name
 */
 
$PluginInfo['Alias'] = array(
   'Name' => 'Alias',
   'Description' => "Keeps track of username changes and shows a list in profile of former names (only useful when \$Configuration['Garden']['Profile']['EditUsernames'] is set to TRUE)",
   'Version' => '0.4',
   'Author' => 'Robin',
   'License' => 'LGPL',
   'MobileFriendly' => TRUE,
   'RequiredApplications' => array('Vanilla' => '>=2.0.18.8')
);

/**
 *  Shows list of former usernames in profile
 */
class AliasPlugin extends Gdn_Plugin {
   /**
    *  Get alias list from UserMeta and displays in profile
    */
   public function UserInfoModule_OnBasicInfo_Handler($Sender) {
      // Get profile user
      $User = GetValue('User', $Sender);
      $UserID = $User->UserID;
      
      // Get UserMeta for profile user
      $UserMeta = Gdn::UserModel()->GetMeta($UserID, 'Alias');
      
      $UserName = $User->Name;
      $AliasList = unserialize($UserMeta['Alias']);

      // only proceed if Alias info exists and is relevant
      if((sizeof($UserMeta['Alias']) != 1) || ($AliasList == array($UserName))) {
         return;
      }
      
      // print out alias info
      echo '<dt class="Alias">'.T('Alias').'</dt>';
      foreach($AliasList as $name => $value) {
         if($UserName != $value) {
            echo '<dd class="Alias">'.$value.'</dd>';
         }
      }
   } // End of  UserInfoModule_OnBasicInfo_Handler
   
   /**
    *  Whenever user changes his profile, add current username to alias list.
    *  Clean up by deleting duplicate names
    *
    */
   public function ProfileController_EditMyAccountAfter_Handler($Sender) {
      // Get logged in user
      $Session = Gdn::Session();
      $UserName = $Session->User->Name;
      $UserID = $Session->UserID;

      // Create UserModel to get access to GetMeta and SetMeta
      $UserModel = Gdn::UserModel();
      // Get alias list
      $UserMeta = $UserModel->GetMeta($UserID, 'Alias');
      $UserAlias = $UserMeta['Alias'];
      $OldAliasList = unserialize($UserAlias);
      $NewAliasList = $OldAliasList;
      // Append current user
      $NewAliasList[] = $UserName;
      // Clear duplicates
      $NewAliasList = array_unique($NewAliasList);
      // Write back info only if is has been modified
      if($NewAliasList != $OldAliasList) {
         $UserModel->SetMeta($UserID, array('Alias' => serialize($NewAliasList)));
      }
   } // End of ProfileController_EditMyAccountAfter_Handler
} // End of AliasPlugin
