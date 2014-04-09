<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
<title><?php title(); ?></title>
<link rel="stylesheet" type="text/css" media="all" href="<?php path('style.css','theme'); ?>" />
<?php head(); ?>
</head>

<body>
<div id="header" class="container_16 clearfix">
	<form id="search" method="post" action="<?php path(array(),'Search'); ?>">
		<div><input type="text" name="word" class="text" size="20" /> <input type="submit" class="submit" value="<?php _e('Search'); ?>" /></div>
	</form>
	<div id="logo">
		<h1><a href="<?php path(); ?>">
		<?php if (logo()): ?>
		<img height="60" src="<?php logo(); ?>" alt="<?php name(); ?>" />
		<?php endif; ?>
		<?php name() ?>
		</a></h1>
		<p class="description"><?php description(); ?></p>
	</div>
</div><!-- end #header -->

<div id="nav_box" class="clearfix">
	<ul class="container_16 clearfix" id="nav_menu">
		<li<?php if(is('Index')): ?> class="current"<?php endif; ?>><a href="<?php path(); ?>"><?php _e('Home'); ?></a></li>
		<?php while(page_next()): ?>
		<li<?php if(page_current()): ?> class="current"<?php endif; ?>><a href="<?php page_link(); ?>" title="<?php page_title(); ?>"><?php page_title(); ?></a></li>
		<?php endwhile; ?>
	</ul>
</div>

<?php echo plugin_call('afterHead'); ?>

<div class="container_16 clearfix">
