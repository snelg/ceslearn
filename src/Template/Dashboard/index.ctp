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
<h1 class="page-header">
    <div class="pull-right">
        <?php if ($this->request->session()->read('Brightspace.Auth')) : ?>
            <?= $this->Html->link('De-authorize Brightspace data', ['controller' => 'Brightspace', 'action' => 'deAuth'], ['class' => 'btn btn-primary']) ?>
        <?php else : ?>
            <?= $this->Html->link('Authorize Brightspace data', ['controller' => 'Brightspace', 'action' => 'auth'], ['class' => 'btn btn-primary']) ?>
        <?php endif ?>
    </div>
    My Learning - Current
</h1>
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
            <div class="panel-body" id="assignmentsLoading"><em>Loading...</em></div>
			<table class="table" id="assignmentsList" style="display: none">
				<thead>
					<tr>
						<th>Due</th>
                        <th>Source</th>
						<th>Name</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-heading">Calendar</div>
			<div class="panel-body">
                <div id="calendar"><em>Loading...</em></div>
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
    <div class="panel-body" id="notificationsLoading"><em>Loading...</em></div>
    <table class="table" id="notificationsList" style="display: none">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
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

        $.ajax('<?= $this->Url->build(['prefix' => 'api', 'controller' => 'Assignments', 'action' => 'index']) ?>', {dataType: 'json'})
            .done(function(data) {
                populateAssignmentList(data);
                $('#calendar').html('').fullCalendar({
                    events: data,
                    displayEventTime: false,
                    eventDataTransform: function(event) {
                        event.start = moment.unix(event.start);
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
                                title: event.title,
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
        $.ajax('<?= $this->Url->build(['prefix' => 'api', 'controller' => 'Notifications', 'action' => 'index']) ?>', {dataType: 'json'})
            .done(function(data) { populateNotificationList(data); });

        function populateAssignmentList(data) {
            $('div#assignmentsLoading').hide();
            var listBody = $('table#assignmentsList').show().find('tbody');
            var datedElements = data.filter(function(e) {
                return (e.start && e.start > <?= time() ?>) ? true : false;
            });
            datedElements.sort(function(a, b) {
                return a.start - b.start;
            });
            for (var i = 0; i < datedElements.length && i < 5; i++) {
                var m = moment.unix(datedElements[i].start);
                listBody.append('<tr><td>' + m.format('MMM D') + '</td><td>' + datedElements[i].source + '</td><td>' + datedElements[i].title + '</td></tr>');
            }
        }

        function populateNotificationList(data) {
            $('div#notificationsLoading').hide();
            var listBody = $('table#notificationsList').show().find('tbody');
            for (var i = 0; i < data.length && i < 5; i++) {
                listBody.append('<tr><td>' + htmlEncode(data[i].subject) + '</td><td>' + htmlEncode(data[i].last_message) + '</td></tr>');
            }
        }

        function htmlEncode(value) {
            return $('<div/>').text(value).html();
        }
	});
<?php $this->Html->scriptEnd() ?>
</script>