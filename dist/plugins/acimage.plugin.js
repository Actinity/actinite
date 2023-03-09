(function () {
	'use strict';

	var global = tinymce.util.Tools.resolve('tinymce.PluginManager');

	/*var register$1 = function (editor) {
		editor.addCommand('InsertHorizontalRule', function () {
			editor.execCommand('mceInsertContent', false, '<hr />');
		});
	};*/

	var register = function (editor) {
		var onAction = function () {

			window.$mitt.emit('assets:open',{
				callback: (img) => {

					if(img) {
						//let width = 800;
						//let height = Math.round(i.height / i.width * width);

						editor.insertContent('<p><img' +
							' src="/storage'+img.path+'"' +
							' data-ac-asset="'+img.id+'"' +
							//' width="'+width+'"' +
							//' height="'+height+'"' +
							'/></p>'
						);
					}


				},
				asset: null,
				type: 'image'
			});

			//return editor.execCommand('InsertHorizontalRule');
		};
		editor.ui.registry.addButton('acimage', {
			icon: 'image',
			tooltip: 'Insert image',
			onAction: onAction
		});
		editor.ui.registry.addMenuItem('acimage', {
			icon: 'image',
			text: 'Insert image',
			onAction: onAction
		});
	};

	function Plugin () {
		global.add('acimage', function (editor) {
			//register$1(editor);
			register(editor);
		});
	}

	Plugin();

}());


