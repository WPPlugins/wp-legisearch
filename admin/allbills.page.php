<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/Bill.class.php' );

?>

<div class="wrap">
	<h1>All Bills <a href="<?php echo get_admin_url(); ?>admin.php?page=legisearch-add-bill" class="page-title-action">Add New</a></h1>
	<?php legisearch_show_messages(); ?>
	<form method="POST" action="#">
	<table class="widefat">
	<thead>
		<th>Description</th><th>Bill</th><th>State</th><th>Session</th>
	</thead>
	<tfoot>
		<th>Description</th><th>Bill</th><th>State</th><th>Session</th>
	</tfoot>
	<tbody>
	<?php foreach( legisearch_get_all_tracked_bills() as $bill ): ?>
		<tr>
			<td class="title column-title has-row-actions column-primary">
				<a class="row-title" href="<?php echo get_admin_url(); ?>admin.php?page=legisearch-bill&bill=<?php echo $bill->id; ?>&action=edit"">
					<strong><?php echo stripslashes( $bill->description ); ?></strong>
				</a>
				<div class="row-actions">
					<span class="edit"><a href="<?php echo get_admin_url(); ?>admin.php?page=legisearch-bill&bill=<?php echo $bill->id; ?>&action=edit">Edit and Votes</a> | </span>
					<span class="trash"><a href="<?php echo get_admin_url(); ?>admin.php?page=legisearch-bill&bill=<?php echo $bill->id; ?>&action=remove">Remove</a></span>
				</div>
			</td>
			<td><?php echo $bill->bill_id; ?></td>
			<td><?php echo $bill->state_name; ?></td>
			<td><?php echo $bill->session_display_name; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
	</td>
	</form>
</div>
