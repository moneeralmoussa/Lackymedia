import sys
import yaml, MySQLdb, json

if len(sys.argv) == 3:
    with open("../app/config/parameters.yml", "r") as stream:
        try:
            parameters = yaml.load(stream)
            activity_days = {}

            connection = MySQLdb.connect(host=parameters["parameters"]["database_host"], db=parameters["parameters"]["database_name"], user=parameters["parameters"]["database_user"], passwd=parameters["parameters"]["database_password"])
            cursor2 = connection.cursor()
            cursor2.execute("SELECT t0_.source AS source_2, t0_.time AS time_3, t0_.mileage AS mileage_6 FROM tracedata t0_ WHERE t0_.time >= %s AND t0_.time < %s AND t0_.type NOT IN ('9', '55', '82', '87', '300', '301', '302') AND t0_.did IS NOT NULL AND t0_.source IS NOT NULL AND t0_.source <> '' AND t0_.mileage IS NOT NULL ORDER BY t0_.source, t0_.time ASC", (sys.argv[1], sys.argv[2], ))

            if cursor2.rowcount > 0:
                for row2 in cursor2:
                    activity_day = row2[1].strftime('%Y-%m-%d')
                    if not row2[0] in activity_days:
                        activity_days[row2[0]] = {}
                    if not activity_day in activity_days[row2[0]]:
                        activity_days[row2[0]][activity_day] = {
                            'date':row2[1].strftime('%d.%m.%Y'),
                            'starttime':row2[1],
                            'startkm':row2[2]/1000.0,
                            'difftime':0,
                            'diffkm':0,
                        }
                    activity_days[row2[0]][activity_day]['endtime'] = row2[1]
                    activity_days[row2[0]][activity_day]['endkm'] = row2[2]/1000.0

            cursor2.close()
            connection.close()

            for activity_source in activity_days:
                for activity_day in activity_days[activity_source]:
                    activity_days[activity_source][activity_day]['diffkm'] = activity_days[activity_source][activity_day]['endkm'] - activity_days[activity_source][activity_day]['startkm']
                    activity_days[activity_source][activity_day]['difftime'] = str(activity_days[activity_source][activity_day]['endtime'] - activity_days[activity_source][activity_day]['starttime'])
                    activity_days[activity_source][activity_day]['starttime'] = activity_days[activity_source][activity_day]['starttime'].strftime('%H:%M:%S')
                    activity_days[activity_source][activity_day]['endtime'] = activity_days[activity_source][activity_day]['endtime'].strftime('%H:%M:%S')

            print json.dumps(activity_days, sort_keys=True)
        except:
            print json.dumps(False)

else:
    print json.dumps(False)
