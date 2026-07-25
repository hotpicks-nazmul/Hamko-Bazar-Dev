<?php if (function_exists("opcache_reset")) { opcache_reset(); echo "opcache_reset OK
"; } if (function_exists("apc_clear_cache")) { apc_clear_cache(); echo "apc OK
"; } echo "Done
";