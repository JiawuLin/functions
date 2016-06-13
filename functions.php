<?php

/**
 * 输出指定数量的换行标签
 * @param int $num
 * @return string tag "<br />"
 */
	function br( $num = 1 )
	{
		return str_repeat("<br />", $num);
	}
