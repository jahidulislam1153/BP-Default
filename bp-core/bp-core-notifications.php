<?php

function bp_core_add_notification( $item_id, $user_id, $component_name, $component_action, $date_notified = false ) {
	global $bp;
	
	if ( !$date_notified )
		$date_notified = time();
		
	$notification = new BP_Core_Notification;
	$notification->item_id = $item_id;
	$notification->user_id = $user_id;
	$notification->component_name = $component_name;
	$notification->component_action = $component_action;
	$notification->date_notified = $date_notified;
	$notification->is_new = 1;

	if ( !$notification->save() )
		return false;
	
	return true;
}

function bp_core_delete_notification( $id ) {
	if ( !bp_core_check_notification_access( $bp['loggedin_userid'], $id ) )
		return false;
	
	return BP_Core_Notification::delete( $id );
}

function bp_core_delete_notifications_for_user_by_type( $user_id, $component_name, $component_action ) {
	return BP_Core_Notification::delete_for_user_by_type( $user_id, $component_name, $component_action );
}

function bp_core_get_notification( $id ) {
	return new BP_Core_Notification( $id ); 
}

function bp_core_get_notifications_for_user( $user_id ) {
	$notifications = BP_Core_Notification::get_all_for_user( $user_id );
		
	/* Group notifications by component and component_action and provide totals */
	for ( $i = 0; $i < count($notifications); $i++ ) {
		$notification = $notifications[$i];
		
		$grouped_notifications[$notification->component_name][$notification->component_action][] = $notification;
	}
	
	if ( !$grouped_notifications )
		return false;
	
	/* Calculated a renderable outcome for each notification type */
	foreach ( $grouped_notifications as $component_name => $action_arrays ) {
		if ( !$action_arrays )
			continue;
		
		foreach ( $action_arrays as $component_action_name => $component_action_items ) {
			$action_item_count = count($component_action_items);
			
			if ( $action_item_count < 1 )
				continue;
			
			$item_id = ( $action_item_count == 1 ) ? $component_action_items[0]->item_id : false;
			
			if ( function_exists( $component_name . '_format_notifications' ) ) {
				$renderable[] = call_user_func( $component_name . '_format_notifications', $component_action_name, $item_id, $action_item_count );
			}
		}
	} 	
	
	return $renderable;
}

function bp_core_check_notification_access( $user_id, $notification_id ) {
	if ( !BP_Core_Notification::check_access( $user_id, $notification_id ) )
		return false;
	
	return true;
}

?>