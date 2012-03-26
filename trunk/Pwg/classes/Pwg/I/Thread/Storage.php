<?php

interface Pm_I_Thread_Storage {
    
    function listThreads($managerId);
    
    function loadData($managerId, $threadId);

    function lock($managerId, $threadId);
    
    function unlock($managerId, $threadId);
    
    function saveData($managerId, $threadId, $data);
    
    function deleteData($managerId, $threadId);
    
} 

?>