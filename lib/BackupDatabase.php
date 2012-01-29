<?php
class BackupDatabase
{
  public static function execute($config, $period)
  {
    foreach($config as $pkg => $pkg_config)
    {
      BackupLogger::indent($pkg);

      if($pkg_config['period'] == $period)
      {
        self::executeSingle($pkg, $pkg_config);
      }
      else
      {
        BackupLogger::append("skipping ({$pkg_config['period']})", 1);
      }

      BackupLogger::back();
    }
  }

  public static function executeSingle($pkg, $config)
  {
    BackupLogger::append('getting database list..', 1);

    switch($config['type'])
    {
      case 'mysql': $list = self::getMysqlDatabaseList($config); break;
      case 'postgres': $list = self::getPostgresDatabaseList($config); break;
      default: $list = array(); break;
    }

    BackupLogger::append(' ..done', 1);

    foreach($list as $db)
    {
      if(in_array($db, $config['exclude']))
      {
        BackupLogger::append("$db excluded", 1);
        continue;
      }

      BackupLogger::indent($db);

      BackupLogger::append("archiving..", 1);
      $filename = Backup::get()->prepareFilename($pkg . '-' . $db, 'sql.bz2');

      switch($config['type'])
      {
        case 'mysql': self::dumpMysqlDatabase($config, $db, $filename); break;
        case 'postgres': self::dumpPostgresDatabase($config, $db, $filename); break;
      }

      BackupEvents::trigger('file', array('filename' => $filename));
      BackupLogger::append(" ..done", 1);

      BackupLogger::back();
    }
  }

  protected static function getMysqlDatabaseList($config)
  {
    if(!($conn = mysql_connect($config['hostname'], $config['username'], $config['password'])))
    {
      throw new Exception('Cannot connect to mysql server!');
    }

    if(!($res = mysql_query('show databases', $conn)))
    {
      throw new Exception('Cannot read database list');
    }

    $list = array();
    while($row = mysql_fetch_assoc($res))
    {
      $list[] = $row['Database'];
    }
    return $list;
  }

  protected static function getPostgresDatabaseList($config)
  {
    $list = array();

    $databases = `su - {$config['sh_user']} -c "psql --quiet --no-align --tuples-only --dbname=postgres --command=\"SELECT datname FROM pg_database WHERE datistemplate IS FALSE AND datallowconn IS TRUE;\""`;
    foreach(explode("\n", $databases) as $db)
    {
      if(empty($db)) continue;

      $list[] = $db;
    }

    return $list;
  }

  protected static function dumpMysqlDatabase($config, $db, $filename)
  {
    `mysqldump -u {$config['username']} -p{$config['password']} -h {$config['hostname']} {$db} | bzip2 -c > "{$filename}"`;
  }

  protected static function dumpPostgresDatabase($config, $db, $filename)
  {
    `su - {$config['sh_user']} -c "pg_dump -c --column-inserts --inserts {$db}" | bzip2 -c > "{$filename}"`;
  }
}