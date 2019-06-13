<?php
namespace app\modules\v1\models;

use Yii;
use yii\db\Expression;
use dektrium\user\models\User as BaseUser;
use yii\web\Request as WebRequest;
use app\modules\v1\jwt\JWT;

class User extends BaseUser
{

	const ROLE_USER = 10;
    const ROLE_ADMIN = 20;
    const ROLE_KIOSK = 30;

    public $excelFile;

    public function rules()
    {
		$rules = parent::rules();
		$rules['avatar'] = [['avatar'], 'safe'];
		$rules['role'] = [['role'], 'safe'];
        $rules['excelFile'] = [['excelFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'xls, xlsx'];
        return $rules;
	}
	
	public function attributeLabels()
    {
		$attributes = parent::attributeLabels();
		$attributes['role'] = 'บทบาท';
        return $attributes;
    }


    /**
     * Store JWT token header items.
     * @var array
     */
    protected static $decodedToken;
    /** @var  string to store JSON web token */
    public $access_token;

    public function generateAccessTokenAfterUpdatingClientInfo($forceRegenerate=false)
    {
        // update client login
        $this->last_login_at = new Expression('UNIX_TIMESTAMP()');

        // check time is expired or not
        if($forceRegenerate == true
            || $this->access_token_expired_at == null
            || (time() > $this->access_token_expired_at))
        {
            // generate access token
            $this->generateAccessToken();
        }
        $this->save(false);
        return true;
    }

    public function generateAccessToken(){
        // generate access token
		$tokens = $this->getJWT();
	    $this->access_token = $tokens[0];   // Token
        $this->access_token_expired_at = $tokens[1]['exp']; // Expire
    }

    public function getJWT()
	{
		// Collect all the data
		$secret      = static::getSecretKey();
		$currentTime = time();
		$expire      = $currentTime + 28800; //28800 8 ชม //1 day 86400
		$request     = Yii::$app->request;
		$hostInfo    = '';
		// There is also a \yii\console\Request that doesn't have this property
		if ($request instanceof WebRequest) {
			$hostInfo = $request->hostInfo;
		}

		// Merge token with presets not to miss any params in custom
		// configuration
		$token = array_merge([
			'iat' => $currentTime,      // Issued at: timestamp of token issuing.
			'iss' => $hostInfo,         // Issuer: A string containing the name or identifier of the issuer application. Can be a domain name and can be used to discard tokens from other applications.
			'aud' => $hostInfo,
			'nbf' => $currentTime,       // Not Before: Timestamp of when the token should start being considered valid. Should be equal to or greater than iat. In this case, the token will begin to be valid 10 seconds
			'exp' => $expire,           // Expire: Timestamp of when the token should cease to be valid. Should be greater than iat and nbf. In this case, the token will expire 60 seconds after being issued.
			'data' => [
                'name' => $this->profile->name,
				'email' => $this->email,
				'username' => $this->username
			]
		], static::getHeaderToken());
		// Set up id
		$token['jti'] = $this->getJTI();    // JSON Token ID: A unique string, could be used to validate a token, but goes against not having a centralized issuer authority.
		return [JWT::encode($token, $secret, static::getAlgo()), $token];
    }

    protected static function getSecretKey()
	{
		return Yii::$app->params['jwtSecretCode'];
    }

    protected static function getHeaderToken()
	{
		return [];
    }

    public function getJTI()
	{
		return $this->getId();
    }

    public static function getAlgo()
	{
		return 'HS256';
    }

    public static function findIdentityByAccessToken($token, $type = null)
	{
	    $user = static::findOne(['auth_key' => $token]);
	    if($user){
	        if($user->getIsBlocked() == true || $user->getIsConfirmed() == false) {
				return null;
			} else {
				return $user;
			}
        }
		$secret = static::getSecretKey();
		// Decode token and transform it into array.
		// Firebase\JWT\JWT throws exception if token can not be decoded
		try {
			$decoded = JWT::decode($token, $secret, [static::getAlgo()]);
		} catch (\Exception $e) {
			return false;
		}
		static::$decodedToken = (array) $decoded;
		// If there's no jti param - exception
		if (!isset(static::$decodedToken['jti'])) {
			return false;
		}
		// JTI is unique identifier of user.
		// For more details: https://tools.ietf.org/html/rfc7519#section-4.1.7
		$id = static::$decodedToken['jti'];
		return static::findByJTI($id);
    }
    
    public static function findByJTI($id)
	{
		/** @var User $user */
		$user = static::find()->where([
			'=', 'id', $id
		])
	    ->andWhere([
	        '>', 'access_token_expired_at', new Expression('UNIX_TIMESTAMP()')
	    ])->one();
		if($user !== null &&
		   ($user->getIsBlocked() == true || $user->getIsConfirmed() == false)) {
			return null;
		}
		return $user;
    }
    
    public function fields()
	{
		return [
			'id',
			'username',
			'email',
			'name' => function ($model) {
				return $model->profile->name;
			},
			'created_at' => function ($model) {
				return Yii::t('user', '{0, date, MMMM dd, YYYY HH:mm}', [$model->created_at]);
			},
			'last_login_at',
			'confirmed_at',
			'avatar',
			'role' => function ($model) {
				return $model->getRoleName();
			},
		];
	}

	public function getRoleName()
    {
        if ($this->role == self::ROLE_ADMIN){
            return 'Admin';
        } elseif ($this->role == self::ROLE_KIOSK){
            return 'Kiosk';
        } else {
			return 'User';
		}
    }

    public function upload()
    {
        if ($this->validate()) {
            $this->excelFile->saveAs('uploads/' . $this->excelFile->baseName . '.' . $this->excelFile->extension);
            return true;
        } else {
            return false;
        }
	}
	
	public function getRole(){
        return $this->role;
    }
}