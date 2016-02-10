<script type="text/javascript">

function dateFomater(date) {
	date = date.getFullYear() + '-' +
		('00' + (date.getMonth()+1)).slice(-2) + '-' +
		('00' + date.getDate()).slice(-2) + ' ' +
		('00' + date.getHours()).slice(-2) + ':' +
		('00' + date.getMinutes()).slice(-2) + ':' +
		('00' + date.getSeconds()).slice(-2);
	return date;
};


function followPost(followingNo, followAction, ifFollowing, f_token) {
	var f_token = '<?php echo $follow_token; ?>';
	if (followAction === 1) {
		var followAction = 'unFollow';
	} else if (followAction === 0) {
		var followAction = 'doFollow';
	}	else {
		return;
	}
	var contact_form_contents = {
		followingNo : followingNo,
		followAction : followAction,
		f_token : f_token
	};
	$.ajax({
		type: 'POST',
		url: '<?php echo $base_url; ?>/ajaxPost/follow',
		data: contact_form_contents,
		success: function(res) {
			var formId = 'follow_form_' + followingNo;
			var elem = document.getElementById(formId);
			if (followAction === 'unFollow') {
				elem.innerHTML = '<input type="hidden" name="followAction" value="follow"><input type="image" class="follow_button" src="<?php echo $base_url; ?>/../img/unfollowed_icon.png" alt="unfollow_button" value="follow">';
			} else if (followAction === 'doFollow') {
				elem.innerHTML = '<input type="hidden" name="followAction" value="follow"><input type="image" class="follow_button" src="<?php echo $base_url; ?>/../img/followed_icon.png" alt="unfollow_button" value="follow">';
			};
		},
		error: function() {
			console.log('ERROR!');
		}
	});
}


function clickObjct(usNo) {
		var clickCount = 1;
		var now = new Date();
		now.setSeconds(now.getSeconds());
		console.log(now);

		var date = now;
		var sqlDate = dateFomater(now);
		var post = {
			sendUser : usNo,
			clickCount : clickCount,
			timestamp : sqlDate,
			date : now
		};
		return post;
};


// Postの値を溜める
clickPool = function (post) {
	var count = 0;
	var posts = {};
	return function(post){
		if (post === 'reset') {
			posts = {};
			count = 0;
			console.log('posts reset!')
			return;
		}
		if (post) { // postがnullなら クロージャー内のpostsを返す
			key = 'no_' + count;
			if (count == 0) {
				posts[key] = post;
				count++;
				var click = 'count_zero';
			} else {
				// 同ユーザーへの時間内連続クリックなら前回のclk値に追加し、ひとまとめに
				var decmentCount = count -1;
				var decmentKey = 'no_' + decmentCount;
				decmentTime = posts[decmentKey].date;
				decmentSeUs = posts[decmentKey].sendUser;
				thisTime = post.date;
				thisSeUs = post.sendUser;
				// 連打判定時間の設定
				if (thisTime - decmentTime <= 5000 && thisSeUs == decmentSeUs) {
					posts[decmentKey].clickCount++;
				} else {
					// オブジェクトの追加
					posts[key] = post;
					count++;
				}
			}
		}
		return posts;
	}
};
var clickPool = clickPool();


function clickPost(posts) {
	if (!posts || posts == 'reset' || typeof(posts) == "function") {
		return;
	}
	var data = {};
	data["clicks"] = posts;
	token = '<?php echo $click_token; ?>';
	data["click_token"] = token;
	var now = new Date();
	now.setSeconds(now.getSeconds());
	var DateTime = dateFomater(now);
	data["postDateTime"] = DateTime;
	$.ajax({
		type: 'POST',
		url: '<?php echo $base_url; ?>/ajaxPost/clickPost',
		data: data,
		success: function(res) {
			console.log('clickPost success!');
			console.log(data);
			clickPool('reset');
		},
		error: function() {
			console.log('clickPost ERROR!');
		}
	})
};


var clickAction = function(action, usNo, usId, usName) {
	if (action == "intervalPost") {
		var posts = clickPool();
		var postsCount = Object.keys(posts).length;
		if (postsCount > 0) {
			clickPost(posts);
			console.log('intervalPost POST end!');
		}
		console.log('intervalPost Check end!');
		return;
	}

	// WebSocketで共有する値
	var msg = {
		usNo : usNo,
		usId : usId,
		usName : usName,
		sendUserNo : <?php echo $this->escape($headerUser['usNo']); ?>,
		sendUserId : '<?php echo $this->escape($headerUser['usId']); ?>',
		sendUserName : '<?php echo $this->escape($headerUser['usName']); ?>',
		sendUserImage : '<?php echo $this->escape($headerUser['usImg']); ?>'
	};
	var msg = JSON.stringify(msg);
	// WebSocket送信
	ID = '#clickAction_' + usNo;
	$(document).on('click', ID, function(){
		socket.send(msg);
	});

	// POST用のオブジェクトを生成
	if (action == 'post') {
		var post = clickObjct(usNo);
		var posts = clickPool(post);
	}

	var postsCount = Object.keys(posts).length;
	// Object数が指定以上で強制POSTさせる
	if (postsCount >= 10) {
		clickPost(posts);
		console.log('postsCount over POST end!');
	}
};

setInterval( "clickAction('intervalPost')" , <?php echo $postSecond; ?> *1000 );

</script>