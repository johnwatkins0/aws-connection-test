<?php

use Aws\S3\S3Client;

require_once __DIR__ . '/vendor/autoload.php';

class S3ConnectionTest {
    /**
     * AWS client.
     * 
     * @var S3Client
     */
    private $client;


    public function __construct() {
        global $argv;

        if ( isset( $argv[2] ) ) {
            $connection_parameters = [
                'roleArn' => $argv[2],
            ];
        } else {
            $connection_parameters = [
                'region' => 'us-west-1',
                'version'   => '2006-03-01',
                'profile' => 'default',
            ];
        }
        
        $this->client = new S3Client( self::getAwsConnectionParameters( $connection_parameters ) );
    }

	public static function getAwsConnectionParameters( $connectionParameters = []) : array
	{
		$connectionParameters;
		
		if ( array_key_exists( "roleArn", $connectionParameters ) )
		{
			$temporaryCredentials = self::assumeRole( $connectionParameters );

			if ( ! is_array( $temporaryCredentials ) )
			{
				throw new Exception( sprintf( "Failed to assume role '%s'.", $connectionParameters['roleArn'] ) );
			}

			$connectionParameters['credentials'] = [
				'key'    => $temporaryCredentials['AccessKeyId'],
				'secret' => $temporaryCredentials['SecretAccessKey'],
				'token'  => $temporaryCredentials['SessionToken']
			];
		}

		return $connectionParameters;
	}

	private static function assumeRole( $connectionParameters ) : array
	{
		$stsClient = new Aws\Sts\StsClient([
			'region' => 'us-east-1',
			'version' => '2011-06-15'
		]);

		$result = $stsClient->AssumeRole([
					'RoleArn'         => $connectionParameters['roleArn'],
					'RoleSessionName' => "wpsnapshots",
		]);

		return $result['Credentials'];
	}

    /**
	 * Tests the user's AWS credentials.
	 *
	 * @param string $repository Repository name.
	 */
	public function test( string $repository ) {
		$bucket_name = sprintf( 'wpsnapshots-%s', $repository );

        try {
            $this->client->listObjects( [ 'Bucket' => $bucket_name ] );
        } catch ( Exception $e ) {
            echo $e->getMessage();
        }
	}
}

$credentials_loader = new S3ConnectionTest();
$credentials_loader->test( $argv[1] );
