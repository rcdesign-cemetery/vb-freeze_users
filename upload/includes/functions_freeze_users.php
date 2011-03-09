<?php
function fu_user_can_be_frozen($user_info)
{
    global $vbulletin;
    
    if (is_member_of($user_info, unserialize($vbulletin->options['fu_source_groups']) AND 
        0 < $vbulletin->options['fu_frozen_group']))
    {
        return true;
    }
    return false;
}


function fu_froze_user($user_info)
{
    global $vbulletin;
    if (fu_user_can_be_frozen($user_info))
    {
        fu_update_subscribes($user_info['userid']);
        $user_dm =& datamanager_init('User', $vbulletin, ERRTYPE_CP);
        $user_dm->set_existing($user_info);
        $user_dm->set('usergroupid', (int)$vbulletin->options['fu_frozen_group']);
        $user_dm->save();
        return true;
    }
    return false;
}

function fu_update_subscribes($sql_part_users)
{
    global $vbulletin;
    if ($vbulletin->products['vbblog'])
    {
        $sql = 'UPDATE 
                    ' . TABLE_PREFIX . 'blog_subscribeuser
                SET
                    `type` = "usercp" 
                WHERE 
                    `type` = "email" AND 
                    `userid` IN (' . $sql_part_users. ')';
        $res = $vbulletin->db->query_write($sql);

        $sql = 'UPDATE 
                    ' . TABLE_PREFIX . 'blog_subscribeentry
                SET
                    `type` = "usercp" 
                WHERE 
                    `type` = "email" AND 
                    `userid` IN (' . $sql_part_users. ')';
        $res = $vbulletin->db->query_write($sql);

    }

    $sql = 'UPDATE 
                ' . TABLE_PREFIX . 'subscribeforum
            SET
                `emailupdate` = 0 
            WHERE 
                `emailupdate` > 0 AND 
                `userid` IN (' . $sql_part_users. ')';
    $res = $vbulletin->db->query_write($sql);

    $sql = 'UPDATE 
                ' . TABLE_PREFIX . 'subscribethread
            SET
                `emailupdate` = 0 
            WHERE 
                `emailupdate` > 0 AND 
                `userid` IN (' . $sql_part_users. ')';
    $res = $vbulletin->db->query_write($sql);

    $sql = 'UPDATE 
                ' . TABLE_PREFIX . 'subscribegroup
            SET
                `emailupdate` = 0 
            WHERE 
                `emailupdate` > 0 AND 
                `userid` IN (' . $sql_part_users. ')';
    $res = $vbulletin->db->query_write($sql);

    $sql = 'UPDATE 
                ' . TABLE_PREFIX . 'subscribediscussion
            SET
                `emailupdate` = 0 
            WHERE 
                `emailupdate` > 0 AND 
                `userid` IN (' . $sql_part_users. ')';

    $res = $vbulletin->db->query_write($sql);

    // clear calendar reminders
    $sql = 'DELETE FROM 
                ' . TABLE_PREFIX . 'subscribeevent 
            WHERE 
                `userid` IN ('. $sql_part_users. ')';
    $res = $vbulletin->db->query_write($sql);
    return true;
}
