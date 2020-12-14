	<div class="toolbar-responsive panel-toolbar">
		<div class="toolbar-wrapper">
		<?php
			$this->Form->unlockField('faculties_id_');
			
			$baseUrl = $this->Url->build([
				'plugin' => $this->request->params['plugin'],
			    'controller' => $this->request->params['controller'],
			    'action' => $this->request->params['action']
			]);
			$template = $this->ControllerAction->getFormTemplate();

			$this->Form->templates($template);

			if (!empty($facultiesOptions)) {
				echo $this->Form->input('faculties_id_', array(
					'class' => 'form-control',
					'label' => false,
					'options' => $facultiesOptions,
					'default' => $selectedFaculties,
					'url' => $baseUrl,
					'data-named-key' => 'faculties_id',
				));
			}

		?>
		</div>
	</div>
