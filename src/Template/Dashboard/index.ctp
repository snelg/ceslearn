<?php
/* @var $this \App\View\AppView */
$this->extend('../Layout/TwitterBootstrap/dashboard');
$this->start('tb_actions');
?>
	<li><?= $this->Html->link('File', ['action' => 'index']) ?></li>
	<li><?= $this->Html->link('Edit', ['action' => 'index']) ?></li>
	<li><?= $this->Html->link('Help', ['action' => 'index']) ?></li>
<?php
$this->end();
?>
<?= $this->Html->css(['jquery.qtip.min', 'cal/fullcalendar.min']) ?>
<?= $this->Html->script(['jquery.qtip.min', 'cal/lib/moment.min', 'cal/fullcalendar.min'], ['block' => 'script']) ?>
<h1 class="page-header">My Learning - Current</h1>
<div class="row">
	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-heading">Goals</div>
			<div class="panel-body">
				<ul style="list-style: none">
					<?php foreach ($goals as $category => $goalList): ?>
						<li>
							<strong><?= h($category) ?></strong>
							<ul style="list-style: none">
								<?php foreach ($goalList as $goal): ?>
									<li>
										<div class="checkbox">
											<?= $this->Form->create($goal, ['url' => ['controller' => 'Goals', 'action' => 'edit']]) ?>
											<label>
												<?= $this->Form->checkbox('complete', ['class' => 'auto-save']) ?>
												<?= h($goal->goal) ?>
											</label>
											<?= $this->Form->end() ?>
										</div>
									</li>
								<?php endforeach ?>
							</ul>
						</li>
					<?php endforeach ?>
				</ul>
			</div>
		</div>
	</div>
	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-heading">Upcoming Assignments</div>
			<table class="table">
				<thead>
					<tr>
						<th>Due</th>
						<th>Name</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($upcomingAssignments as $assignment): ?>
						<tr>
							<td><?= empty($assignment->due_at) ? '' : date('D, M j, Y', $assignment->due_at) ?></td>
							<td><?= h($assignment->name) ?></td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-body">
				<div id="calendar"></div>
			</div>
		</div>
	</div>
	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-heading">Notes</div>
			<div class="panel-body">
				<?= $this->Form->create($note, ['url' => ['controller' => 'Notes', 'action' => 'edit']]) ?>
				<?= $this->Form->textarea('note', ['class' => 'auto-save form-control']) ?>
				<?= $this->Form->end() ?>
			</div>
		</div>
	</div>
</div>
<div class="panel panel-default">
			<div class="panel-heading">Notifications</div>
			<table class="table">
				<thead>
					<tr>
						<th>Due</th>
						<th>Name</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($notifications as $notification): ?>
						<tr>
							<td><?= $notification->subject ?></td>
							<td><?= $notification->last_message?></td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>
<script>
<?php $this->Html->scriptStart(['block' => true]) ?>
	$(document).ready(function() {
		$('.auto-save').change(function() {
			var form = $(this).closest('form');
			$.ajax({
				type: "POST",
				url: form.attr('action'),
				dataType: 'json',
				data: form.serialize()
			})
				.done(function(data) {
					//Don't think we need to any UI stuff here
				})
				.fail(function() {
					//maybe tell the user something funky?
				})
		})

        $('#calendar').fullCalendar({
			events: <?= json_encode((array)$assignments) ?>,
            displayEventTime: false,
			eventDataTransform: function(event) {
                event.url = event.html_url;
				event.title = event.name;
				event.start = moment.unix(event.due_at);
                return event;
            },
            eventRender: function(event, element) {
				var tmp = document.createElement('div');
				tmp.innerHTML = event.description;
				var description = tmp.textContent || tmp.innerHTML;
                if (description && description.length > 200) {
                    description = description.substring(0, 200) + ' [...]';
                }
                element.qtip({
                    content: {
                        title: event.name,
                        text: description},
                    style: {classes: 'qtip-bootstrap'},
                    show: {solo: true},
                    position: {
                        my: 'center left',
                        at: 'top right'
                    }
                });
            }
		});
	});
<?php $this->Html->scriptEnd() ?>
</script>