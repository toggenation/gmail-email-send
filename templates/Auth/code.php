<?php

?>

<dl>
    <dt>Code</dt>
    <dd><?php echo $params['code']; ?></dd>
    <dt>Scope</dt>
    <dd><?php echo implode('<br>', explode(' ', $params['scope'])); ?></dd>
</dl>