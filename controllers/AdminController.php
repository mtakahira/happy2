<?php
class AdminController extends Controller
{
	// ログインが必要なActionを記述登録
	protected $auth_actions = array(
		'index',
		'tableCommand',
		'DummyInOneClick',
		'RandomMaker',
		'getAdminSetting',
		'signout',
		'PtDefault',
		'tbgvnPosts'
	);

	public $tableNames = array('tbus', 'tbgvn', 'tbset', 'tbfollow', 'tbcalctime', 'tb_user_status');
	public $commands = array('Reset', 'DummyIn', 'DummysIn');

	public function indexAction()
	{
		$session = $this->session->get('admin');
		if (!$session) {
			return $this->redirect('/');
		}

		$admin_repository = $this->db_manager->get('Admin');
		$adsetting_repository = $this->getAdminSettingAction();

		$setting = $adsetting_repository->fetchSettingValue();
		$limit = $setting['adminTablesViewLimit'];
		$P = $admin_repository->allUsersPtsSum();
		$allUsersPtsSum = $P['nowPt'];

		$last = $admin_repository->lastCalcTime();
		$lastCalcTime = $last['date'];

		$time = $admin_repository->getCalcTimeBetweenAndLastTime();
		$lastTime = $time[0]['calctime'];
		isset($time[1]['calctime']) ? $lastButOne = $time[1]['calctime'] : $lastButOne = null;
		$getPtsSum = $admin_repository->getSumPtsLastTime($lastTime, $lastButOne);

		$allUsersPtsSum_tbset = floatval($getPtsSum['userPts']);

		// ***ToDo*** pager機能の分離
		$tables = [];
		$tbCounts = [];
		$key = [];
		$pages = [];
		$offsets = [];

		$getpage = intval($this->request->getGet('page'));

		foreach ($this->tableNames as $tableName) {

			$tb = $this->request->getGet('table');
			if ($tb == $tableName) {
				$pages[$tableName] = $getpage;
			}

			if (empty($pages[$tableName])) {
				$pages[$tableName] = 0;
			}

			$nextpages[$tableName] = $pages[$tableName] + 1;
			$prevpages[$tableName] = $pages[$tableName] - 1;
			$offsets[$tableName] = $limit * $pages[$tableName];

			if ($tableName == 'tbus') {
				// PassWord非表示の為の別処理
				$tables[$tableName] = $admin_repository->fetchAlltbus($limit, $offsets[$tableName]);
			} else {
				$tables[$tableName] = $admin_repository->fetchAllTable($tableName, $limit, $offsets[$tableName]);
			}

			if (!$admin_repository->tableCount($tableName)) {
				$key = array_merge($key, array($tableName => 'no Table!'));
			} else{
				$key = $admin_repository->tableCount($tableName);
			}
			$tbCounts += array($tableName => $key[$tableName]);
		}

		return $this->render(array(
			'body' => '',
			'allUsersPtsSum' => $allUsersPtsSum,
			'allUsersPtsSum_tbset' => $allUsersPtsSum_tbset,
			'tableNames' => $this->tableNames,
			'commands' => $this->commands,
			'tables' => $tables,
			'tbCounts' => $tbCounts,
			'pages' => $pages,
			'offsets' => $offsets,
			'nextpages' => $nextpages,
			'prevpages' => $prevpages,
			'limit' => $limit,
			'_token' => $this->generateCsrfToken('admin/post'),
			'hostName' => $_SERVER['HOST_NAME'],
		));
	}

	// table操作を集約 Reset,DummyIn を各テーブルで行わせる
	public function tableCommandAction()
	{
		$token = $this->request->getPost('_token');
		if (!$this->checkCsrfToken('admin/post', $token)) {
			return $this->redirect('/');
		}

		$tableName = $this->request->getPost('tableName');
		$command = $this->request->getPost('command');
		$admin_repository = $this->db_manager->get('Admin');
		$dummyNames = $this->RandomMakerAction();
		// AdminRepossitoryのメソッド名を生成
		$RepossitoryCommnd = $tableName.$command;
		if (!method_exists('AdminRepository', $RepossitoryCommnd)) {
			$this->forward404();
		}
		$admin_repository->$RepossitoryCommnd($dummyNames);

		// 仕様上ダミーアカウント作成直後に行うアクション
		// 必ず自分に1クリック・tb_user_statusに連携登録
		if ($RepossitoryCommnd == 'tbusDummyIn') {
			$this->DummyInOneClickAction($dummyNames['usId']);
			$this->DummyIn_tb_user_statusInsert($dummyNames['usId']);
		}
		if ($RepossitoryCommnd == 'tbusDummysIn') {
			$this->DummyInOneClickAction();
		}

		return $this->redirect('/admin/index#anchor_'.$tableName);
	}

	public function DummyInOneClickAction($userId = null)
	{
		if ($userId) {
			$ID = [];
			$ID[0] = $userId;
		} else {
			$ID = array('itinose', 'nikaidou', 'mitaka', 'yotuya', 'godai', 'roppngi', 'nanase', 'yagami', 'kujou', 'otonashi');
		}

		$admin_repository = $this->db_manager->get('Admin');
		$a = 0;
		while ($a < count($ID)) {
			$res = $admin_repository->getDummyUserNo($ID[$a]);
			$admin_repository->tbusDummyIn_SelfOneClick($res['usNo']);
			$a++;
		}
	}

	public function DummyIn_tb_user_statusInsert($userId)
	{
		$AdminRepository = $this->db_manager->get('Admin');

		$res = $AdminRepository->getDummyUserNo($userId);
		$usNo = $res['usNo'];
		$user_repository = $this->db_manager->get('User');
		$latitude = null;
		$longitude = null;
		$user_repository->tb_user_statusRegisterInsert($usNo, $latitude, $longitude);
	}

	public function RandomMakerAction() {
		$length = 8;

		//Thanks!! http://qiita.com/TetsuTaka/items/bb020642e75458217b8a
		static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
		$str = '';
		for ($i = 0; $i < $length; ++$i) {
			$str .= $chars[mt_rand(0, 61)];
		}

		// Thanks!! http://mitsuakikawamorita.com/blog/?p=1095
		$hiragana = 'あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよらりるれろわおん';
		$kanji = '右雨王音火貝九玉金月犬見口左山子糸耳車手十女人水夕石川早足大竹虫天田土日年白文木目立力六一ニ三四五下七小上生中入八本円休出森正赤千男町名林花学気空校字青先草村百'; // 小学1年生で習う漢字

		$jp_literals = $hiragana . $kanji;
		$usName = "";
		for ($i = 0; $i < $length; $i++) {
			$usName .= mb_substr($jp_literals, mt_rand(0, mb_strlen($jp_literals, "UTF-8") - 1), 1, "utf-8");
		}

		$admin_repository = $this->db_manager->get('Admin');
		$res = $admin_repository->tableCount('tbus');
		$userCount = $res['tbus'];
		$usId = $str;
		$no1 = rand(1,10);
		$no2 = rand(1,10);
		$no3 = rand(1,$userCount);
		$clk = rand(1,30);
		$dummys = array('no1' => $no1, 'no2' => $no2, 'no3' => $no3, 'userCount' => $userCount, 'clk' => $clk, 'usId' =>$usId, 'usName' =>$usName);

		return $dummys;
	}

	public function getAdminSettingAction()
	{
//		$session = $this->session->get('admin');
//		if (!$session) {
//			return $this->redirect('/');
//		}
		$adsetting_repository = $this->db_manager->get('AdminSetting');
		return $adsetting_repository;
	}

	// ***ToDo***
	public function pagerAction($table, $page, $offset)
	{
		$setting = $adsetting_repository->fetchSettingValue();
		$limit = $setting['adminTablesViewLimit'];

		$page = $this->request->getGet('tbus');

		if (empty($page)) {
			$page = 0;
		}
		$nextpage = $page + 1;
		$prevpage = $page - 1;
		$offset = $limit * $page;

		$pager = array($offset, $limit, $page);
		return $pager;
	}

	public function signoutAction()
	{
		$this->session->clear();
		$this->session->setAuthenticated(false);

		return $this->redirect('/admin/signin');
	}

	public function PtDefaultAction()
	{
		$session = $this->session->get('admin');
		if (!$session) {
			return $this->redirect('/');
		}

		$token = $this->request->getPost('_token');
		if (!$this->checkCsrfToken('admin/post', $token)) {
			return $this->redirect('/');
		}

		$adsetting_repository = $this->getAdminSettingAction();
		$setting = $adsetting_repository->fetchSettingValue();
		$DefaultPt = $setting['userDefaultPt'];
		$admin_repository = $this->db_manager->get('Admin');
		$admin_repository->PtDefault_tbus($DefaultPt);
		$this->redirect('/admin/index');
	}

	public function tbgvnPostsAction()
	{
		if (!$this->request->isPost()) {
			$this->forward404();
		}
		$session = $this->session->get('admin');
		if (!$session) {
			return $this->redirect('/');
		}

		$token = $this->request->getPost('_token');
		if (!$this->checkCsrfToken('admin/post', $token)) {
			return $this->redirect('/');
		}

		$dummys['no1'] = $this->request->getPost('no1');
		$dummys['no2'] = $this->request->getPost('no2');
		$dummys['clk'] = $this->request->getPost('clk');
		$admin_repository = $this->db_manager->get('Admin');
		$admin_repository->tbgvnDummyIn($dummys);
		$this->redirect('/admin/index#anchor_tbgvn');
	}

	public function calcAction()
	{
		if (!$this->request->isPost()) {
			$this->forward404();
			error_log("clac button POST token unmatch!! no exec calcAction");
		}

		$buttonToken = $this->request->getPost('_token');
		$postCronToken = $this->request->getPost('cronToken');

		if ($buttonToken || $postCronToken) {

			if ($buttonToken) {
				if (!$this->checkCsrfToken('admin/post', $buttonToken)) {
					error_log("clac button POST token unmatch!! no exec calcAction $_POST dump data >>> " . print_r($_POST));
					// 管理画面からのボタン押下でNGの場合
					return $this->redirect('/');
				}
			}

			if ($postCronToken) {
				if ($_SERVER['CRON_TOKEN'] !== $postCronToken) {
					// cron処理でNGの場合 ひとまずlogだけ残す。
					return error_log("clac cron token unmatch!! no exec calcAction. $_POST dump data >>> " . print_r($_POST));
				}
			}

		} else {
			return error_log("Irregular POST data !! no exec calcAction. $_POST dump data >>> " . print_r($_POST));
		}

		$date = new DateTime();
		$now = $date->format('Y-m-d H:i:s');
		$admin_repository = $this->db_manager->get('Admin');
		$last = $admin_repository->lastCalcTime();
		$last['date']  ?  $lastCalcTime = $last['date']  :  $lastCalcTime = $now;

		// 最終集計時間からClickの更新が無い場合、集計をキャンセル
		if(!$admin_repository->checkTodayClick($lastCalcTime)){
			if ($buttonToken) {
				error_log('!!!button calc cancel!!!');
				$this->redirect('/admin/index');
			}
			if ($postCronToken) {
				error_log('!!!cron calc cancel!!!');
				return;
			}
		}

		$admin_repository->startTransaction();
		// 集計期間の重複防止の為+1秒で登録
		$admin_repository->tbcalctimeInsertPlus1sec();

		// *** 集計1段階 クリックしたユーザーのポイントを分配***

		// clkしたユーザーの合計クリック数とnowPt
		$clkUsersStatus = $admin_repository->clkUsersClkSumAndPts($lastCalcTime, $now);
		$N = 0;
		// ClkをしたuserNのPtを全クリック数に従い分配,tbsetにinsert
		foreach ($clkUsersStatus as $noUse) {
			$userNo = $clkUsersStatus[$N]['usNo'];
			$nowPts = $clkUsersStatus[$N]['nowPt'];
			// ユーザーiがレコード毎にクリックした数を算出
			$sendClksSumToUser = $admin_repository->sendClksSumToUser($lastCalcTime, $now, $userNo);
			$i = 0;
			foreach ($sendClksSumToUser as $noUse) {
				$setGvnNo = $sendClksSumToUser[$i]['gvnNo'];
				$usNo = $sendClksSumToUser[$i]['usNo'];
				$seUs = $sendClksSumToUser[$i]['seUs'];
				$seClk = $sendClksSumToUser[$i]['seClk'];
				$dTm = $sendClksSumToUser[$i]['dTm'];
				// clkしたユーザーの1クリックあたりのPt計算
				// ユーザーNへのクリック数 / 全ユーザーへのクリック数 ＊ 現在のポイント
				$getPt = $seClk / $clkUsersStatus[$N]['clk_sum'] * $clkUsersStatus[$N]['nowPt'];
				$admin_repository->clkUsersPts_tbsetInsert($setGvnNo, $usNo, $seUs, $getPt, $dTm);
				$i++;
			}
			$N++;
		}
		$admin_repository->TransactionCommit();

		// ***集計第二段階 nowPtのベーシックインカム的な補正計算***

		$adsetting_repository = $this->getAdminSettingAction();
		$setting = $adsetting_repository->fetchSettingValue();
		$minPt = intval($setting['userMinPt']);
		$defaultPt = intval($setting['userDefaultPt']);
		$DefaultPt = intval($setting['userDefaultPt']);

		// 全ユーザーのPt合計誤差の補正値を求める
		$tableCount = $admin_repository->tableCount('tbus');
		$AlluserCount = intval($tableCount['tbus']);
		$getPtsSum = $admin_repository->getCalcResultSumPts($lastCalcTime, $now);
		$PtsSum = floatval($getPtsSum['userPts']);
		$AllPtsTolerance = round($AlluserCount * $DefaultPt - $PtsSum , 9);

		// echo '集計結果合計 getPtsSum<br>';
		// var_dump($PtsSum);
		// echo '最終的な補正値<br>';
		// var_dump($AllPtsTolerance);
		// echo '全ユーザー数<br>';
		// var_dump($AlluserCount);
		// echo '初期値<br>';
		// var_dump($DefaultPt);

		$userPts = [];
		$sendUsersNo = [];	//対象のusNo
		$differencePts = [];	//差分
		$shortPts = [];	//不足分
		$surplusPts = [];	//余剰分

		// 実質全ユーザー対象(登録時self1クリックしてる)
		$sendUsersGetPtsSum = $admin_repository->sendUsersGetPtsSum($lastCalcTime, $now);

		// 最低値ちょうど以外のユーザーの最低値からの差と人数を求める
		$u = 0;
		$plusUser = 0;
		foreach ($sendUsersGetPtsSum as $user[$u]) {
			$userPts[$u] = floatval($user[$u]['getPt']);

			if ($userPts[$u] !== floatval($minPt)) {
				$sendUsersNo[$u] = $user[$u]['seUs'];
				$differencePts[$u] = $userPts[$u] - $minPt;

				if ($userPts[$u] < $minPt) {
					$shortPts[$u] = $minPt - $userPts[$u];
				} else {
					$surplusPts[$u] = $userPts[$u] - $minPt;
					$plusUser++;
				}
				$u++;
			}
		}

		// ユーザー総計Ptの 全余剰&全不足
		$shortPtsSum = array_sum($shortPts);
		// 全体誤差の修正値をマイナス
		$surplusPtsSum = array_sum($surplusPts) - $AllPtsTolerance;

		// 各ユーザーの補正Ptを求る
		$rivisePts = [];
		for ($i=0; $i < $u ; $i++) {
			if ($differencePts[$i] > 0) {
				// 保持Ptへの全体値誤差修正（Pt総数が逸脱した際の保険的処理）
				$userPts[$i] = $userPts[$i] + $AllPtsTolerance / $plusUser;
				// (過) 負担する値 = 保持Pt - (保持Pt + 余剰Pt * (全不足Pt合計 / 全余剰Pt合計))
				$rivisePts[$i] = $userPts[$i] - ($userPts[$i] + $surplusPts[$i] * ($shortPtsSum / $surplusPtsSum));
			} else {
				// (不足) 最低値との差分
				$rivisePts[$i] = $minPt - $userPts[$i];
			}
		}

		// echo '$sendUsersNo Ptを送られたユーザーNo一覧<br>';
		// var_dump($sendUsersNo);
		// echo '<br>$userPts Ptを送られたユーザーの取得Pt<br>';
		// var_dump($userPts);
		// echo '<br>$differencePts 差分Pt<br>';
		// var_dump($differencePts);
		// echo '<br>$shortPts 不足Pt<br>';
		// var_dump($shortPts);
		// echo '<br>$surplusPts 余剰分Pt<br>';
		// var_dump($surplusPts);
		// echo '<br>$shortPtsSum 不足Pt合計<br>';
		// var_dump($shortPtsSum);
		// echo '<br>$surplusPtsSum 余剰Pt合計<br>';
		// var_dump($surplusPtsSum);
		// echo '<br>$sendUsersNo Ptを送られたユーザーNo一覧<br>';
		// var_dump($sendUsersNo);
		// echo '<br>$rivisePts Ptを送られたユーザーの補正Pt算出<br>';
		// var_dump($rivisePts);

		// 補正PtのDB INSERT userNo=0 からのPtとして1ユーザーに全誤差を付与
		// ***ToDo*** 誤差が大きい場合は他のユーザーにもPtの補正負荷分散を
		$admin_repository->startTransaction();
		$admin_repository->clkUsersRivisePts_TogetherInsert($sendUsersNo, $rivisePts, $now);
		$admin_repository->TransactionCommit();
		if (abs($AllPtsTolerance - 0) >= 0.000000001) {
			if ($AllPtsTolerance > 0) {
				$order = 'ASC';
			} else {
				$order = 'DESC';
			}
				$user = $admin_repository->getAllToleranceUser($lastCalcTime, $now, $order);
				$usNo = $user[0]['seUs'];
				$admin_repository->ToleranceInsert($usNo, $AllPtsTolerance, $now);
		}

		// 集計結果を取得
		$calcResultPts = $admin_repository->getCalcResultPts($lastCalcTime, $now);

		$userNo = [];
		$nowPts = [];
		$admin_repository->startTransaction();
		$u = 0;
		foreach ($calcResultPts as $noUse ) {
			$userNo = $calcResultPts[$u]['seUs'];
			$nowPts = $calcResultPts[$u]['userPts'];
			// 集計結果をtbusに更新反映
			// ***ToDo*** 1回のinsertクエリ処理で可能か調査、改修
			$admin_repository->calcResultPts_tbusInsert($nowPts, $userNo);
			$u++;
		}
		$admin_repository->TransactionCommit();

		// 全ユーザーに自分に1クリックさせる
		$startTime = $admin_repository->lastCalcTime();
		$startTime = $startTime['date'];
		$usersNo = $admin_repository->getAllUserNo();
		$admin_repository->startTransaction();
		$admin_repository->allUserSelfOneClick($usersNo, $startTime);
		$admin_repository->TransactionCommit();

		// $this->redirect('/admin/index');

		if ($buttonToken) {
			return $this->render(array(
				'body' => '',
				'lastCalcTime' => $lastCalcTime,
				'clkUsersStatus' => $clkUsersStatus,
				'_token' => $this->generateCsrfToken('admin/post'),
			));
		}
	}
}
