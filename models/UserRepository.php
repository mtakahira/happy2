<?php

class UserRepository extends DbRepository
{

	public function insert($usId, $usPs, $usName)
	{
		$usPs = $this->hashPassword($usPs);
		$now = new DateTime();
		$img =  "dummy.png";
		$nowPt = AdminSettingRepository::userDefaultPoint;
		$ip = $_SERVER['REMOTE_ADDR'];
		$host = gethostbyaddr($ip);

		$sql = "
			INSERT INTO tbus (usId,usPs,usName,usImg,nowPt,ip,host,regDate)
			VALUES(:usId, :usPs, :usName, '$img', '$nowPt', '$ip', '$host', :regDate)
		";

		$stmt = $this->execute($sql, array(
			':usId' => $usId,
			':usPs' => $usPs,
			':usName' => $usName,
			':regDate' => $now->format('Y-m-d H:i:s'),
		));
	}

	public function getUserNo($usId)
	{
		$sql = "
			SELECT usNo, usId
			FROM tbus
			WHERE usId = :usId
		";

		return $this->fetch($sql, array(
			':usId' => $usId,
		));
	}

	public function selfOneClick($usNo)
	{
		$sql = "
			INSERT INTO tbgvn(usNo, seUs, seClk, dTm)
			VALUES($usNo, $usNo, 1, now())
		";

		$stmt = $this->execute($sql, array(
		));
	}

	public function clickPost($usNo, $clicks)
	{
		$sql = "
			INSERT INTO
				tbgvn(usNo, seUs, seClk, dTm)
				VALUES
		";

		$count = count($clicks);
		$a = 0;
		if (count($clicks) >= 2) {
			while ($a < count($clicks) - 1) {
				$no = 'no_' . $a;
				$seUs = $clicks[$no]['sendUser'];
				$seClk = $clicks[$no]['clickCount'];
				$dTm = $clicks[$no]['timestamp'];
				// ***ToDo*** POST時間の修正
				$sql .= "($usNo, $seUs, $seClk, '$dTm'),";
				$a++;
			}
		}

		$no = 'no_' . $a;
		$seUs = $clicks[$no]['sendUser'];
		$seClk = $clicks[$no]['clickCount'];
		$dTm = $clicks[$no]['timestamp'];
		// ***ToDo*** POST時間の修正
		$sql .= "($usNo, $seUs, $seClk, '$dTm');";

		$stmt = $this->execute($sql, array(
			':usNo' => $usNo,
			':seUs' => $seUs,
			':seClk' => $seClk,
			':dTm' => $dTm,
		));
	}

	public function tb_user_statusRegisterInsert($usNo, $latitude, $longitude)
	{
		$sql = "
			INSERT INTO tb_user_status(usNo, lastActiveTime, latitude, longitude)
			VALUES(:usNo, now(), :latitude, :longitude)
		";

		$stmt = $this->execute($sql, array(
			':usNo' => $usNo,
			':latitude' => $latitude,
			':longitude' => $longitude,
		));
	}

	public function hashPassword($usPs)
	{
		return sha1($usPs . 'zyx7532cba');
	}

	public function fetchByUserName($usId)
	{
		$sql = "SELECT * FROM tbus WHERE usId = :usId";

		return $this->fetch($sql, array(':usId' => $usId));
	}

	public function isUniqueUserName($usId)
	{
		$sql = "SELECT COUNT(usNo) as count FROM tbus WHERE usId = :usId";

		$row = $this->fetch($sql, array(':usId' => $usId));
		if ($row['count'] === '0') {
			return true;
		}

		return false;
	}

	public function fetchAllFollowingsByUserId($usId)
	{
		$sql = "
			SELECT u.*
			FROM user u
				LEFT JOIN following f ON f.following_id = u.id
			WHERE f.usId = :usId
		";

		return $this->fetchAll($sql, array(':usId' => $usId));
	}

}
