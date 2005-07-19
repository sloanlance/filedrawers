// JavaScript Document
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

function give_submit(val)
{
    document.changesupport.give_support.value = val;
    document.changesupport.submit();
}

function remove_submit(val)
{
    document.changesupport.remove_support.value = val;
    document.changesupport.submit();
}
