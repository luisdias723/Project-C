<?php 
namespace App\Services;

use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;

class SftpService{


    protected $sftp;

    public function __construct()
    {
        $ssh = new SSH2('xhovox.ddns.net', 2526);
        $ssh->login('st_app', 'Sapp##hhoovvoo@0523');
        $this->sftp= new SFTP($ssh);
    }



}



?>