#include "stdafx.h"
#include "s_COdbcManager.h"

#ifdef _DEBUG
#define new DEBUG_NEW
#endif

/**
* User Database
*/

__time64_t COdbcManager::GetChatBlockTime(int nUserNum)
{
	if (nUserNum <= 0) return DB_ERROR;

	ODBC_STMT* pConn = m_pUserDB->GetConnection();
	if (!pConn)	return DB_ERROR;

    SQLRETURN sReturn=0;

    //std::strstream strTemp;	

	TCHAR szTemp[128] = {0};
	
	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "SELECT ChatBlockDate FROM DaumUserInfo WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "SELECT ChatBlockDate FROM DaumUserInfo WHERE UserNum=%d", nUserNum );
		break;
	case SP_JAPAN:
		_snprintf( szTemp, 128, "SELECT ChatBlockDate FROM JapanUserInfo WHERE UserNum=%d", nUserNum );
		break;
	case SP_TERRA :
		//strTemp << "SELECT ChatBlockDate FROM TerraUserInfo WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "SELECT ChatBlockDate FROM TerraUserInfo WHERE UserNum=%d", nUserNum );
		break;
	case SP_MALAYSIA :
		//strTemp << "SELECT ChatBlockDate FROM TerraUserInfo WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "SELECT ChatBlockDate FROM TerraUserInfo WHERE UserNum=%d", nUserNum );
		break;
	case SP_THAILAND :
		//strTemp << "SELECT ChatBlockDate FROM UserInfo WHERE UserNum=";
		//strTemp << nUserNum ;
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "SELECT ChatBlockDate FROM UserInfo WHERE UserNum=%d", nUserNum );
		break;
	case SP_GLOBAL:
		//strTemp << "SELECT ChatBlockDate FROM GspUserInfo WHERE UserNum=";
		//strTemp << nUserNum ;
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "SELECT ChatBlockDate FROM GspUserInfo WHERE UserNum=%d", nUserNum );
		break;
	case SP_GS :
		_snprintf( szTemp, 128, "SELECT ChatBlockDate FROM GSUserInfo WHERE UserNum=%d", nUserNum );
		break;
	default :
		//strTemp << "SELECT ChatBlockDate FROM UserInfo WHERE UserNum=";
		//strTemp << nUserNum ;
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "SELECT ChatBlockDate FROM UserInfo WHERE UserNum=%d", nUserNum );
		break;
	}

    TIMESTAMP_STRUCT sChatBlockDate; SQLINTEGER cbChatBlockDate = SQL_NTS;
    __time64_t tChatBlockDate = 0;

    sReturn = ::SQLExecDirect(pConn->hStmt,
							  (SQLCHAR*) szTemp, 
							  SQL_NTS);

	if ((sReturn != SQL_SUCCESS) && (sReturn != SQL_SUCCESS_WITH_INFO)) 
	{
        Print(szTemp);
		Print(GetErrorString(pConn->hStmt));
		m_pUserDB->FreeConnection(pConn);

		//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

        return DB_ERROR;
	}

    while (true)
	{
		sReturn = ::SQLFetch(pConn->hStmt);
		if (sReturn == SQL_ERROR || sReturn == SQL_SUCCESS_WITH_INFO)
        {
            Print(szTemp);
			Print(GetErrorString(pConn->hStmt));
            m_pUserDB->FreeConnection(pConn);

			//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

            return DB_ERROR;
		}

        if (sReturn == SQL_SUCCESS || sReturn == SQL_SUCCESS_WITH_INFO)
		{
            ::SQLGetData(pConn->hStmt, 1, SQL_C_TYPE_TIMESTAMP, &sChatBlockDate, 0, &cbChatBlockDate);

			VerifyTimeValid( "COdbcManager::GetChatBlockTime", "sChatBlockDate", sChatBlockDate );
           
            CTime cTemp(sChatBlockDate.year, sChatBlockDate.month,  sChatBlockDate.day, 
                        sChatBlockDate.hour, sChatBlockDate.minute, sChatBlockDate.second);
            tChatBlockDate = cTemp.GetTime();
        }
		else
		{
			break;
		}
		Sleep( 0 );
	}
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

    m_pUserDB->FreeConnection(pConn);
    return tChatBlockDate;
}

int COdbcManager::SetChatBlockTime(int nUserNum, __time64_t tBlockTime)
{
	if (nUserNum <= 0) return DB_ERROR;
	
	CTime cTemp(tBlockTime);
	CString strTime = cTemp.Format(_T("%Y-%m-%d %H:%M:%S"));

	//std::strstream strTemp;

	TCHAR szTemp[256] = {0};
	
	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "UPDATE DaumUserInfo SET ChatBlockDate='";
		//strTemp << strTime.GetString() << "' WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE DaumUserInfo SET ChatBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;	
	case SP_JAPAN :
		//_snprintf( szTemp, 256, "UPDATE ExciteUserInfo SET ChatBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 256, "UPDATE JapanUserInfo SET ChatBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	case SP_TERRA :
		//strTemp << "UPDATE TerraUserInfo SET ChatBlockDate='";
		//strTemp << strTime.GetString() << "' WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE TerraUserInfo SET ChatBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	case SP_MALAYSIA :
		//strTemp << "UPDATE TerraUserInfo SET ChatBlockDate='";
		//strTemp << strTime.GetString() << "' WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE TerraUserInfo SET ChatBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	case SP_THAILAND :
		//strTemp << "UPDATE UserInfo SET ChatBlockDate='";
		//strTemp << strTime.GetString() << "' WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE UserInfo SET ChatBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	case SP_GLOBAL:
		//strTemp << "UPDATE GspUserInfo SET ChatBlockDate='";
		//strTemp << strTime.GetString() << "' WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE GspUserInfo SET ChatBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	case SP_GS :
		//strTemp << "UPDATE UserInfo SET ChatBlockDate='";
		//strTemp << strTime.GetString() << "' WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE GSUserInfo SET ChatBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	default :
		//strTemp << "UPDATE UserInfo SET ChatBlockDate='";
		//strTemp << strTime.GetString() << "' WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE UserInfo SET ChatBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	}

	int nReturn = m_pUserDB->ExecuteSQL(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

	return nReturn;
}

///////////////////////////////////////////////////////////////////////////////
// �ش� ������� �����̾� �Ⱓ�� �����´�.
__time64_t COdbcManager::GetPremiumTime(int nUserNum)
{
	if (nUserNum <= 0) return DB_ERROR;

    ODBC_STMT* pConn = m_pUserDB->GetConnection();
	if (!pConn)	return DB_ERROR;

    SQLRETURN sReturn=0;
	//std::strstream strTemp;

	TCHAR szTemp[128] = {0};
    
	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "SELECT PremiumDate FROM DaumUserInfo WHERE UserNum=";
		//strTemp << nUserNum ;
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "SELECT PremiumDate FROM DaumUserInfo WHERE UserNum=%d", nUserNum );
		break;
	case SP_TERRA :
		//strTemp << "SELECT PremiumDate FROM TerraUserInfo WHERE UserNum=";
		//strTemp << nUserNum ;
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "SELECT PremiumDate FROM TerraUserInfo WHERE UserNum=%d", nUserNum );
		break;
	case SP_MALAYSIA :
		//strTemp << "SELECT PremiumDate FROM TerraUserInfo WHERE UserNum=";
		//strTemp << nUserNum ;
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "SELECT PremiumDate FROM TerraUserInfo WHERE UserNum=%d", nUserNum );
		break;
	case SP_JAPAN :
		//_snprintf( szTemp, 128, "SELECT PremiumDate FROM ExciteUserInfo WHERE UserNum=%d", nUserNum );
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 128, "SELECT PremiumDate FROM JapanUserInfo WHERE UserNum=%d", nUserNum );
		break;
	case SP_THAILAND :
		//strTemp << "SELECT PremiumDate FROM UserInfo WHERE UserNum=";
		//strTemp << nUserNum ;
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "SELECT PremiumDate FROM UserInfo WHERE UserNum=%d", nUserNum );
		break;
	case SP_GLOBAL:
		//strTemp << "SELECT PremiumDate FROM GspUserInfo WHERE UserNum=";
		//strTemp << nUserNum ;
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "SELECT PremiumDate FROM GspUserInfo WHERE UserNum=%d", nUserNum );
		break;
	case SP_GS :
		//strTemp << "SELECT PremiumDate FROM UserInfo WHERE UserNum=";
		//strTemp << nUserNum ;
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "SELECT PremiumDate FROM GSUserInfo WHERE UserNum=%d", nUserNum );
		break;
	default :
		//strTemp << "SELECT PremiumDate FROM UserInfo WHERE UserNum=";
		//strTemp << nUserNum ;
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "SELECT PremiumDate FROM UserInfo WHERE UserNum=%d", nUserNum );
		break;
	}

    TIMESTAMP_STRUCT sPremiumDate; SQLINTEGER cbPremiumDate = SQL_NTS;
    __time64_t tPremiumDate = 0;

    sReturn = ::SQLExecDirect(pConn->hStmt,
							(SQLCHAR*) szTemp, 
							SQL_NTS);

	if ((sReturn != SQL_SUCCESS) && (sReturn != SQL_SUCCESS_WITH_INFO)) 
	{
        Print(szTemp);
		Print(GetErrorString(pConn->hStmt));
		m_pUserDB->FreeConnection(pConn);

		//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

        return DB_ERROR;
	}

    while (true)
	{
		sReturn = ::SQLFetch(pConn->hStmt);
		if (sReturn == SQL_ERROR || sReturn == SQL_SUCCESS_WITH_INFO)
        {
            Print(szTemp);
			Print(GetErrorString(pConn->hStmt));
            m_pUserDB->FreeConnection(pConn);

			//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

            return DB_ERROR;
		}

        if (sReturn == SQL_SUCCESS || sReturn == SQL_SUCCESS_WITH_INFO)
		{
            ::SQLGetData(pConn->hStmt, 1, SQL_C_TYPE_TIMESTAMP, &sPremiumDate, 0, &cbPremiumDate);

			VerifyTimeValid( "COdbcManager::GetPremiumTime", "sPremiumDate", sPremiumDate );
          
            CTime cTemp(sPremiumDate.year, sPremiumDate.month,  sPremiumDate.day, 
                        sPremiumDate.hour, sPremiumDate.minute, sPremiumDate.second);
            tPremiumDate = cTemp.GetTime();
        }
		else
		{
			break;
		}
		Sleep( 0 );
	}
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

    m_pUserDB->FreeConnection(pConn);
    return tPremiumDate;
}

/**
 * �ش� ������� �����̾� �Ⱓ�� �����Ѵ�.
 */
int COdbcManager::SetPremiumTime(int nUserNum, __time64_t tPremiumTime)
{
	if (nUserNum <= 0) return DB_ERROR;	

	CTime cTemp(tPremiumTime);
	CString strTime = cTemp.Format("%Y-%m-%d %H:%M:%S");

	//std::strstream strTemp;

	TCHAR szTemp[256] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "UPDATE DaumUserInfo SET PremiumDate='";
		//strTemp << strTime.GetString() << "' WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE DaumUserInfo SET PremiumDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	case SP_JAPAN :
		//_snprintf( szTemp, 256, "UPDATE ExciteUserInfo SET PremiumDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 256, "UPDATE JapanUserInfo SET PremiumDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	case SP_TERRA :
		//strTemp << "UPDATE TerraUserInfo SET PremiumDate='";
		//strTemp << strTime.GetString() << "' WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE TerraUserInfo SET PremiumDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;	
	case SP_MALAYSIA :
		//strTemp << "UPDATE TerraUserInfo SET PremiumDate='";
		//strTemp << strTime.GetString() << "' WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE TerraUserInfo SET PremiumDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	case SP_THAILAND :
		//strTemp << "UPDATE UserInfo SET PremiumDate='";
		//strTemp << strTime.GetString() << "' WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE UserInfo SET PremiumDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	case SP_GLOBAL:
		//strTemp << "UPDATE GspUserInfo SET PremiumDate='";
		//strTemp << strTime.GetString() << "' WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE GspUserInfo SET PremiumDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	case SP_GS :
		//strTemp << "UPDATE UserInfo SET PremiumDate='";
		//strTemp << strTime.GetString() << "' WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE GSUserInfo SET PremiumDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;	
	default:
		//strTemp << "UPDATE UserInfo SET PremiumDate='";
		//strTemp << strTime.GetString() << "' WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE UserInfo SET PremiumDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;		
	} // switch (nServiceProvider)

	int nReturn = m_pUserDB->ExecuteSQL(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

	return nReturn;
}

/**
 * 0 �Ϲݻ����
 * 10 Ư������� (���ӹ� ��ü ����, ���� ���)
 * 20 GM 3 ��
 * 21 GM 2 ��
 * 22 GM 1 ��
 * 30 Master
 */
int COdbcManager::GetUserType(int nUserNum)
{
	if (nUserNum <= 0) return 0;
	
	//std::strstream strTemp;

	TCHAR szTemp[128] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "{call daum_user_gettype(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call daum_user_gettype(%d, ?)}", nUserNum );
		break;
	case SP_JAPAN :
		//_snprintf( szTemp, 128, "{call excite_user_gettype(%d, ?)}", nUserNum );
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 128, "{call Japan_user_gettype(%d, ?)}", nUserNum );
		break;
	case SP_TERRA :
		//strTemp << "{call terra_user_gettype(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call terra_user_gettype(%d, ?)}", nUserNum );
		break;
	case SP_MALAYSIA :
		//strTemp << "{call terra_user_gettype(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call terra_user_gettype(%d, ?)}", nUserNum );
		break;
	case SP_THAILAND :
		//strTemp << "{call user_gettype(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call user_gettype(%d, ?)}", nUserNum );
		break;
	case SP_GLOBAL:
		//strTemp << "{call gsp_user_gettype(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call gsp_user_gettype(%d, ?)}", nUserNum );
		break;
	case SP_GS :
		//strTemp << "{call user_gettype(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call GS_user_gettype(%d, ?)}", nUserNum );
		break;
	default :
		//strTemp << "{call user_gettype(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call user_gettype(%d, ?)}", nUserNum );
		break;
	} // switch (nServiceProvider)
	
	int nReturn = m_pUserDB->ExecuteSpInt(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

	return nReturn;
}

/**
 * �ش������� ���� �� �ִ� ĳ���� ������ �����´�.
 */
int COdbcManager::GetUserChaRemain(int nUserNum)
{
	if (nUserNum <= 0) return 0;

	//std::strstream strTemp;

	TCHAR szTemp[128] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "{call daum_user_cha_remain(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call daum_user_cha_remain(%d, ?)}", nUserNum );
		break;
	case SP_JAPAN :		
		//_snprintf( szTemp, 128, "{call excite_user_cha_remain(%d, ?)}", nUserNum );
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 128, "{call Japan_user_cha_remain(%d, ?)}", nUserNum );
		break;
	case SP_TERRA :
		//strTemp << "{call terra_user_cha_remain(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call terra_user_cha_remain(%d, ?)}", nUserNum );
		break;
	case SP_MALAYSIA :
		//strTemp << "{call terra_user_cha_remain(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call terra_user_cha_remain(%d, ?)}", nUserNum );
		break;
	case SP_THAILAND :
		//strTemp << "{call user_cha_remain(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call user_cha_remain(%d, ?)}", nUserNum );
		break;
	case SP_GLOBAL:
		//strTemp << "{call gsp_user_cha_remain(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call gsp_user_cha_remain(%d, ?)}", nUserNum );
		break;
	case SP_GS :
		//strTemp << "{call user_cha_remain(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call GS_user_cha_remain(%d, ?)}", nUserNum );
		break;
	default :
		//strTemp << "{call user_cha_remain(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call user_cha_remain(%d, ?)}", nUserNum );
		break;
	} // switch (m_nServiceProvider)

	int nReturn = m_pUserDB->ExecuteSpInt(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

    // ĳ���ͻ������� ����
    if (nReturn < 0 || nReturn > MAX_CHAR_LENGTH)
    {
        nReturn = 0;
    }
	return nReturn;
}

/**
 * Test �������� ���� �� �ִ� ĳ���� ����
 */
int	COdbcManager::GetUserChaTestRemain(int nUserNum)
{
	if (nUserNum <= 0) return 0;
	
	//std::strstream strTemp;
	TCHAR szTemp[128] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "{call daum_user_cha_test_remain(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call daum_user_cha_test_remain(%d, ?)}", nUserNum );
		break;
	case SP_JAPAN :
		//_snprintf( szTemp, 128, "{call excite_user_cha_test_remain(%d, ?)}", nUserNum );
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 128, "{call Japan_user_cha_test_remain(%d, ?)}", nUserNum );
		break;
	case SP_TERRA :
		//strTemp << "{call terra_user_cha_test_remain(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call terra_user_cha_test_remain(%d, ?)}", nUserNum );
		break;
	case SP_MALAYSIA :
		//strTemp << "{call terra_user_cha_test_remain(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call terra_user_cha_test_remain(%d, ?)}", nUserNum );
		break;
	case SP_GLOBAL:
		//strTemp << "{call gsp_user_cha_test_remain(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call gsp_user_cha_test_remain(%d, ?)}", nUserNum );
		break;
	default:
		//strTemp << "{call user_cha_test_remain(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call user_cha_test_remain(%d, ?)}", nUserNum );
		break;
	}

	int nReturn=0;
	
	nReturn = m_pUserDB->ExecuteSpInt(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

    // ĳ���ͻ������� ����
    if (nReturn < 0 || nReturn > MAX_CHAR_LENGTH)
    {
        nReturn = 0;
    }
	return nReturn;
}

///////////////////////////////////////////////////////////////////////////
// �ش����ڸ� Logout �Ѵ�
int	COdbcManager::UserLogout(
	const TCHAR* szUsrID, 
	int nUsrNum, 
	int nGameTime,
	int nChaNum,
	int nSvrGrp,
	int nSvrNum, 
	int nTotalGameTime,
	int nOfflineTime )
{	
	if (nGameTime == 0)	nGameTime = 1;

	//std::strstream strTemp;

	TCHAR szTemp[256] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :		
		//strTemp << "{call daum_user_logout(";
		//strTemp << "'" << szUsrID << "',";
		//strTemp << nUsrNum << ",";
		//strTemp << nGameTime << ",";
		//strTemp << nChaNum << ",";
		//strTemp << nSvrGrp << ",";
		//strTemp << nSvrNum << ")}";
		//strTemp << std::ends;
//#if	defined(_RELEASED)
//		_snprintf( szTemp, 256, "{call user_logout('%s',%d,%d,%d,%d,%d,%d,%d)}", szUsrID, nUsrNum, nGameTime, nChaNum, nSvrGrp, nSvrNum, nTotalGameTime, nOfflineTime );
//#else
		_snprintf( szTemp, 256, "{call daum_user_logout('%s',%d,%d,%d,%d,%d)}", szUsrID, nUsrNum, nGameTime, nChaNum, nSvrGrp, nSvrNum );
//#endif
		break;

	case SP_CHINA:
		_snprintf( szTemp, 256, "{call user_logout('%s',%d,%d,%d,%d,%d,%d,%d)}", szUsrID, nUsrNum, nGameTime, nChaNum, nSvrGrp, nSvrNum, nTotalGameTime, nOfflineTime );
		break;

	case SP_JAPAN : 
		//_snprintf( szTemp, 256, "{call excite_user_logout('%s',%d,%d,%d,%d,%d)}", szUsrID, nUsrNum, nGameTime, nChaNum, nSvrGrp, nSvrNum );
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 256, "{call Japan_user_logout('%s',%d,%d,%d,%d,%d)}", szUsrID, nUsrNum, nGameTime, nChaNum, nSvrGrp, nSvrNum );
		break;
	case SP_TERRA : 
		//strTemp << "{call terra_user_logout(";
		//strTemp << "'" << szUsrID << "',";
		//strTemp << nUsrNum << ",";
		//strTemp << nGameTime << ",";
		//strTemp << nChaNum << ",";
		//strTemp << nSvrGrp << ",";
		//strTemp << nSvrNum << ")}";
		//strTemp << std::ends;
#if	defined(VN_PARAM)
		_snprintf( szTemp, 256, "{call terra_user_logout('%s',%d,%d,%d,%d,%d,%d,%d)}", szUsrID, nUsrNum, nGameTime, nChaNum, nSvrGrp, nSvrNum, nTotalGameTime, nOfflineTime );
#else
		_snprintf( szTemp, 256, "{call terra_user_logout('%s',%d,%d,%d,%d,%d)}", szUsrID, nUsrNum, nGameTime, nChaNum, nSvrGrp, nSvrNum );
#endif
		break;
	case SP_MALAYSIA : 
		//strTemp << "{call terra_user_logout(";
		//strTemp << "'" << szUsrID << "',";
		//strTemp << nUsrNum << ",";
		//strTemp << nGameTime << ",";
		//strTemp << nChaNum << ",";
		//strTemp << nSvrGrp << ",";
		//strTemp << nSvrNum << ")}";
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "{call terra_user_logout('%s',%d,%d,%d,%d,%d)}", szUsrID, nUsrNum, nGameTime, nChaNum, nSvrGrp, nSvrNum );
		break;
	case SP_THAILAND :
		//strTemp << "{call user_logout(";
		//strTemp << "'" << szUsrID << "',";
		//strTemp << nUsrNum << ",";
		//strTemp << nGameTime << ",";
		//strTemp << nChaNum << ",";
		//strTemp << nSvrGrp << ",";
		//strTemp << nSvrNum << ")}";
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "{call user_logout('%s',%d,%d,%d,%d,%d)}", szUsrID, nUsrNum, nGameTime, nChaNum, nSvrGrp, nSvrNum );
		break;
	case SP_GLOBAL:
		//strTemp << "{call gsp_user_logout(";
		//strTemp << "'" << szUsrID << "',";
		//strTemp << nUsrNum << ",";
		//strTemp << nGameTime << ",";
		//strTemp << nChaNum << ",";
		//strTemp << nSvrGrp << ",";
		//strTemp << nSvrNum << ")}";
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "{call gsp_user_logout('%s',%d,%d,%d,%d,%d)}", szUsrID, nUsrNum, nGameTime, nChaNum, nSvrGrp, nSvrNum );
		break;
	case SP_GS :
		//strTemp << "{call user_logout(";
		//strTemp << "'" << szUsrID << "',";
		//strTemp << nUsrNum << ",";
		//strTemp << nGameTime << ",";
		//strTemp << nChaNum << ",";
		//strTemp << nSvrGrp << ",";
		//strTemp << nSvrNum << ")}";
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "{call GS_user_logout('%s',%d,%d,%d,%d,%d)}", szUsrID, nUsrNum, nGameTime, nChaNum, nSvrGrp, nSvrNum );
		break;
	default :
  //      strTemp << "{call user_logout(";
		//strTemp << "'" << szUsrID << "',";
		//strTemp << nUsrNum << ",";
		//strTemp << nGameTime << ",";
		//strTemp << nChaNum << ",";
		//strTemp << nSvrGrp << ",";
		//strTemp << nSvrNum << ")}";
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "{call user_logout('%s',%d,%d,%d,%d,%d)}", szUsrID, nUsrNum, nGameTime, nChaNum, nSvrGrp, nSvrNum );		
//		_snprintf( szTemp, 256, "{call user_logout('%s',%d,%d,%d,%d,%d,%d,%d)}", szUsrID, nUsrNum, nGameTime, nChaNum, nSvrGrp, nSvrNum, nTotalGameTime, nOfflineTime );
		break;
	} // switch (nServiceProvider)

	int nReturn = m_pUserDB->ExecuteSp(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

	return nReturn;
}

/**
 * ����� ID �� �̿��ؼ� ����ڸ� �α׾ƿ� ��Ų��
 */
int COdbcManager::UserLogoutSimple( const TCHAR* szUsrID )
{
	if (szUsrID == NULL) return DB_ERROR;	

	//std::strstream strTemp;

	TCHAR szTemp[128] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA : // UserUID
		//strTemp << "{call Daum_UserLogoutSimple('";
		//strTemp << szUsrID << "')}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Daum_UserLogoutSimple('%s')}", szUsrID );
		break;
	case SP_JAPAN : // UserID 
		//strTemp << "{call Excite_UserLogoutSimple('";
		//strTemp << szUsrID << "')}";
		//strTemp << std::ends;
//		_snprintf( szTemp, 128, "{call Excite_UserLogoutSimple('%s')}", szUsrID );
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 128, "{call Japan_UserLogoutSimple('%s')}", szUsrID );
		break;
	case SP_TERRA : // [TLoginName]
		//strTemp << "{call Terra_UserLogoutSimple('";
		//strTemp << szUsrID << "')}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Terra_UserLogoutSimple('%s')}", szUsrID );
		break;
	case SP_MALAYSIA : // [TLoginName]
		//strTemp << "{call Terra_UserLogoutSimple('";
		//strTemp << szUsrID << "')}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Terra_UserLogoutSimple('%s')}", szUsrID );
		break;
	case SP_THAILAND: // Thailand
		//strTemp << "{call UserLogoutSimple('";
		//strTemp << szUsrID << "')}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call UserLogoutSimple('%s')}", szUsrID );
		break;
	case SP_GLOBAL: // GSP
		//strTemp << "{call Gsp_UserLogoutSimple('";
		//strTemp << szUsrID << "')}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Gsp_UserLogoutSimple('%s')}", szUsrID );
		break;
	case SP_GS: // GS
		//strTemp << "{call UserLogoutSimple('";
		//strTemp << szUsrID << "')}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call GS_UserLogoutSimple('%s')}", szUsrID );
		break;
	default :
		//strTemp << "{call UserLogoutSimple('";
		//strTemp << szUsrID << "')}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call UserLogoutSimple('%s')}", szUsrID );
		break;
	} // switch (nServiceProvider)

	int nReturn = m_pUserDB->ExecuteSp(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

	return nReturn;
}

/**
 * ����� ��ȣ�� �̿��ؼ� ����ڸ� �α׾ƿ� ��Ų��
 */
int COdbcManager::UserLogoutSimple2(int nUserNum)
{	
	if (nUserNum <= 0) return DB_ERROR;	

	//std::strstream strTemp;

	TCHAR szTemp[128] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "{call Daum_UserLogoutSimple2(";
		//strTemp << nUserNum;
		//strTemp << ")}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Daum_UserLogoutSimple2(%d)}", nUserNum );
		break;
	case SP_JAPAN :
		//strTemp << "{call Excite_UserLogoutSimple2(";
		//strTemp << nUserNum;
		//strTemp << ")}";
		//strTemp << std::ends;
//		_snprintf( szTemp, 128, "{call Excite_UserLogoutSimple2(%d)}", nUserNum );
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 128, "{call Japan_UserLogoutSimple2(%d)}", nUserNum );
		break;
	case SP_TERRA :
		//strTemp << "{call Terra_UserLogoutSimple2(";
		//strTemp << nUserNum;
		//strTemp << ")}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Terra_UserLogoutSimple2(%d)}", nUserNum );
		break;
	case SP_MALAYSIA :
		//strTemp << "{call Terra_UserLogoutSimple2(";
		//strTemp << nUserNum;
		//strTemp << ")}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Terra_UserLogoutSimple2(%d)}", nUserNum );
		break;
	case SP_THAILAND :
		//strTemp << "{call UserLogoutSimple2(";
		//strTemp << nUserNum;
		//strTemp << ")}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call UserLogoutSimple2(%d)}", nUserNum );
		break;
	case SP_GLOBAL:
		//strTemp << "{call Gsp_UserLogoutSimple2(";
		//strTemp << nUserNum;
		//strTemp << ")}";		
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Gsp_UserLogoutSimple2(%d)}", nUserNum );
		break;
	case SP_GS :
		//strTemp << "{call UserLogoutSimple2(";
		//strTemp << nUserNum;
		//strTemp << ")}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call GS_UserLogoutSimple2(%d)}", nUserNum );
		break;
	default :
		//strTemp << "{call UserLogoutSimple2(";
		//strTemp << nUserNum;
		//strTemp << ")}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call UserLogoutSimple2(%d)}", nUserNum );
		break;
	} // switch (nServiceProvider)

	int nReturn = m_pUserDB->ExecuteSp(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

	return nReturn;
}

// �ش� ���Ӽ����� ��ü ������ �α׾ƿ� ��Ų��.
int	COdbcManager::UserLogoutSvr(int nSvrGrp, 
								int nSvrNum)
{
	//std::strstream strTemp;

	TCHAR szTemp[256] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "UPDATE DaumUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=";
		//strTemp << nSvrGrp;
		//strTemp << " AND SvrNum=";
		//strTemp << nSvrNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE DaumUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d AND SvrNum=%d", nSvrGrp, nSvrNum );
		break;
	case SP_JAPAN :
		//strTemp << "UPDATE ExciteUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=";
		//strTemp << nSvrGrp;
		//strTemp << " AND SvrNum=";
		//strTemp << nSvrNum;
		//strTemp << std::ends;
//		_snprintf( szTemp, 256, "UPDATE ExciteUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d AND SvrNum=%d", nSvrGrp, nSvrNum );
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 256, "UPDATE JapanUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d AND SvrNum=%d", nSvrGrp, nSvrNum );
		break;
	case SP_TERRA :
		//strTemp << "UPDATE TerraUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=";
		//strTemp << nSvrGrp;
		//strTemp << " AND SvrNum=";
		//strTemp << nSvrNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE TerraUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d AND SvrNum=%d", nSvrGrp, nSvrNum );
		break;
	case SP_MALAYSIA :
		//strTemp << "UPDATE TerraUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=";
		//strTemp << nSvrGrp;
		//strTemp << " AND SvrNum=";
		//strTemp << nSvrNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE TerraUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d AND SvrNum=%d", nSvrGrp, nSvrNum );
		break;
	case SP_THAILAND :
		//strTemp << "UPDATE UserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=";
		//strTemp << nSvrGrp;
		//strTemp << " AND SvrNum=";
		//strTemp << nSvrNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE UserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d AND SvrNum=%d", nSvrGrp, nSvrNum );
		break;
	case SP_GLOBAL:
		//strTemp << "UPDATE GspUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=";
		//strTemp << nSvrGrp;
		//strTemp << " AND SvrNum=";
		//strTemp << nSvrNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE GspUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d AND SvrNum=%d", nSvrGrp, nSvrNum );
		break;
	case SP_GS :
		//strTemp << "UPDATE UserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=";
		//strTemp << nSvrGrp;
		//strTemp << " AND SvrNum=";
		//strTemp << nSvrNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE GSUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d AND SvrNum=%d", nSvrGrp, nSvrNum );
		break;
	default :
		//strTemp << "UPDATE UserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=";
		//strTemp << nSvrGrp;
		//strTemp << " AND SvrNum=";
		//strTemp << nSvrNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE UserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d AND SvrNum=%d", nSvrGrp, nSvrNum );
		break;
	} // switch (nServiceProvider)

	int nReturn = m_pUserDB->ExecuteSQL(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

    return nReturn;
}

int COdbcManager::UserUpdateCha( int nUsrNum, const TCHAR* szChaName )
{
	//std::strstream strTemp;
	TCHAR szTemp[128] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "{call Daum_UpdateChaName(";
		//strTemp << nUsrNum << ",'";
		//strTemp << szChaName << "')}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Daum_UpdateChaName(%d,'%s')}", nUsrNum, szChaName );
		break;
	case SP_JAPAN :
		//strTemp << "{call Excite_UpdateChaName(";
		//strTemp << nUsrNum << ",'";
		//strTemp << szChaName << "')}";
		//strTemp << std::ends;
		//_snprintf( szTemp, 128, "{call Excite_UpdateChaName(%d,'%s')}", nUsrNum, szChaName );
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 128, "{call Japan_UpdateChaName(%d,'%s')}", nUsrNum, szChaName );
		break;
	case SP_TERRA :
		//strTemp << "{call Terra_UpdateChaName(";
		//strTemp << nUsrNum << ",'";
		//strTemp << szChaName << "')}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Terra_UpdateChaName(%d,'%s')}", nUsrNum, szChaName );
		break;
	case SP_MALAYSIA :
		//strTemp << "{call Terra_UpdateChaName(";
		//strTemp << nUsrNum << ",'";
		//strTemp << szChaName << "')}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Terra_UpdateChaName(%d,'%s')}", nUsrNum, szChaName );
		break;
	case SP_THAILAND :
		//strTemp << "{call UpdateChaName(";
		//strTemp << nUsrNum << ",'";
		//strTemp << szChaName << "')}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call UpdateChaName(%d,'%s')}", nUsrNum, szChaName );
		break;
	case SP_GLOBAL:
		//strTemp << "{call Gsp_UpdateChaName(";
		//strTemp << nUsrNum << ",'";
		//strTemp << szChaName << "')}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Gsp_UpdateChaName(%d,'%s')}", nUsrNum, szChaName );
		break;
	case SP_GS :
		//strTemp << "{call UpdateChaName(";
		//strTemp << nUsrNum << ",'";
		//strTemp << szChaName << "')}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call GS_UpdateChaName(%d,'%s')}", nUsrNum, szChaName );
		break;
	default :
		//strTemp << "{call UpdateChaName(";
		//strTemp << nUsrNum << ",'";
		//strTemp << szChaName << "')}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call UpdateChaName(%d,'%s')}", nUsrNum, szChaName );
		break;
	} // switch (nServiceProvider)

	int nReturn = m_pUserDB->ExecuteSp(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

	return nReturn;
}

/**
* �ش� id ������� ����� ��ȣ�� �����´�.
*/
int COdbcManager::GetUserNum( const TCHAR* szUsrId )
{
	if (szUsrId == NULL) return DB_ERROR;

	SQLRETURN	sReturn = 0;
	ODBC_STMT* pConn = m_pUserDB->GetConnection();
	if (!pConn)	return DB_ERROR;

	SQLINTEGER nUserNum=0, cbUserNum=SQL_NTS;

	//std::strstream strTemp;	

	TCHAR szTemp[256] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "SELECT UserNum FROM DaumUserInfo  WHERE UserUID='" << szUsrId << "'";
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "SELECT UserNum FROM DaumUserInfo  WHERE UserUID='%s'", szUsrId );
		break;
	case SP_JAPAN :
		//strTemp << "SELECT UserNum FROM ExciteUserInfo WHERE UserID='" << szUsrId << "'";
		//strTemp << std::ends;
//		_snprintf( szTemp, 256, "SELECT UserNum FROM ExciteUserInfo  WHERE UserUID='%s'", szUsrId );		
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 256, "SELECT UserNum FROM JapanUserInfo  WHERE UserUID='%s'", szUsrId );
		break;
	case SP_TERRA :
		//strTemp << "SELECT UserNum FROM TerraUserInfo WHERE TLoginName='" << szUsrId << "'";
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "SELECT UserNum FROM TerraUserInfo  WHERE UserUID='%s'", szUsrId );
		break;
	case SP_MALAYSIA :
		//strTemp << "SELECT UserNum FROM TerraUserInfo WHERE TLoginName='" << szUsrId << "'";
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "SELECT UserNum FROM TerraUserInfo  WHERE UserUID='%s'", szUsrId );
		break;
	case SP_THAILAND :
		//strTemp << "SELECT UserNum FROM UserInfo WHERE UserID='" << szUsrId << "'";
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "SELECT UserNum FROM UserInfo  WHERE UserUID='%s'", szUsrId );
		break;
	case SP_GLOBAL:
		//strTemp << "SELECT UserNum FROM GspUserInfo WHERE UserID='" << szUsrId << "'";
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "SELECT UserNum FROM GspUserInfo  WHERE UserUID='%s'", szUsrId );
		break;
	case SP_GS :
		//strTemp << "SELECT UserNum FROM UserInfo WHERE UserID='" << szUsrId << "'";
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "SELECT UserNum FROM GSUserInfo  WHERE UserUID='%s'", szUsrId );
		break;
	default:		
		//strTemp << "SELECT UserNum FROM UserInfo WHERE UserID='" << szUsrId << "'";
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "SELECT UserNum FROM UserInfo  WHERE UserUID='%s'", szUsrId );
		break;
	}

	sReturn = ::SQLExecDirect(pConn->hStmt, 
							  (SQLCHAR*) szTemp, 
							  SQL_NTS);
	if ((sReturn != SQL_SUCCESS) && (sReturn != SQL_SUCCESS_WITH_INFO)) 
	{
        Print(szTemp);
		Print(GetErrorString(pConn->hStmt));
		m_pUserDB->FreeConnection(pConn);

		//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

		return DB_ERROR;
	}
	else
	{
		while(true)
		{
			sReturn = ::SQLFetch(pConn->hStmt);
			if (sReturn == SQL_ERROR || sReturn == SQL_SUCCESS_WITH_INFO)
			{
                Print(szTemp);
		        Print(GetErrorString(pConn->hStmt));
				m_pUserDB->FreeConnection(pConn);

				//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

				return DB_ERROR;
			}
 			if (sReturn == SQL_SUCCESS || sReturn == SQL_SUCCESS_WITH_INFO)
			{
				::SQLGetData(pConn->hStmt, 1, SQL_C_LONG, &nUserNum, 0, &cbUserNum);				
			}
			else
			{
				break;
			}
		}

		//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.
	}
	m_pUserDB->FreeConnection(pConn);
	return nUserNum;
}

int COdbcManager::AddBlockIPList( TCHAR* szIP, TCHAR* szReason )
{
	//std::strstream strTemp;
	//strTemp << "INSERT INTO BlockAddress (BlockAddress, BlockReason) VALUES('" << szIP << "', '" << szReason << "')";
	//strTemp << std::ends;

	TCHAR szTemp[256] = {0};
	_snprintf( szTemp, 256, "INSERT INTO BlockAddress (BlockAddress, BlockReason) VALUES('%s', '%s')", szIP, szReason ); 

	int nReturn = m_pUserDB->ExecuteSQL(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

	return nReturn;
}

/**
 * ĳ���� ����, ���� ����
 */
int	COdbcManager::UserChaNumDecrease(int nUserNum)
{
	if (nUserNum <= 0) return DB_ERROR;	

	std::strstream strTemp;
	TCHAR szTemp[128] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "{call Daum_UpdateChaNumDecrease(";
		//strTemp << nUserNum;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Daum_UpdateChaNumDecrease(%d, ?)}", nUserNum );
		break;
	case SP_JAPAN :
		//strTemp << "{call Excite_UpdateChaNumDecrease(";
		//strTemp << nUserNum;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		//_snprintf( szTemp, 128, "{call Excite_UpdateChaNumDecrease(%d, ?)}", nUserNum );		
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 128, "{call Japan_UpdateChaNumDecrease(%d, ?)}", nUserNum );
		break;
	case SP_TERRA :
		//strTemp << "{call Terra_UpdateChaNumDecrease(";
		//strTemp << nUserNum;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Terra_UpdateChaNumDecrease(%d, ?)}", nUserNum );
		break;
	case SP_MALAYSIA :
		//strTemp << "{call Terra_UpdateChaNumDecrease(";
		//strTemp << nUserNum;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Terra_UpdateChaNumDecrease(%d, ?)}", nUserNum );
		break;
	case SP_THAILAND :
		//strTemp << "{call UpdateChaNumDecrease(";
		//strTemp << nUserNum;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call UpdateChaNumDecrease(%d, ?)}", nUserNum );
		break;
	case SP_GLOBAL:
		//strTemp << "{call Gsp_UpdateChaNumDecrease(";
		//strTemp << nUserNum;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Gsp_UpdateChaNumDecrease(%d, ?)}", nUserNum );
		break;
	case SP_GS :
		//strTemp << "{call UpdateChaNumDecrease(";
		//strTemp << nUserNum;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call GS_UpdateChaNumDecrease(%d, ?)}", nUserNum );
		break;
	default :
		//strTemp << "{call UpdateChaNumDecrease(";
		//strTemp << nUserNum;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call UpdateChaNumDecrease(%d, ?)}", nUserNum );
		break;
	} // switch (nServiceProvider)

	int nReturn = m_pUserDB->ExecuteSpInt(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

	return nReturn;
}

/*
 * TEST ���� ĳ���� �������� ����
 */
int	COdbcManager::UserTestChaNumDecrease(int nUserNum)
{
	if (nUserNum <= 0) return DB_ERROR;	

	//std::strstream strTemp;

	TCHAR szTemp[128] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "{call Daum_UpdateTestChaNumDecrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Daum_UpdateTestChaNumDecrease(%d, ?)}", nUserNum );
		break;
	case SP_JAPAN :
		//strTemp << "{call Excite_UpdateTestChaNumDecrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		//_snprintf( szTemp, 128, "{call Excite_UpdateTestChaNumDecrease(%d, ?)}", nUserNum );		
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 128, "{call Japan_UpdateTestChaNumDecrease(%d, ?)}", nUserNum );
		break;
	case SP_TERRA :
		//strTemp << "{call Terra_UpdateTestChaNumDecrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Terra_UpdateTestChaNumDecrease(%d, ?)}", nUserNum );
		break;
	case SP_MALAYSIA :
		//strTemp << "{call Terra_UpdateTestChaNumDecrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Terra_UpdateTestChaNumDecrease(%d, ?)}", nUserNum );
		break;
	case SP_THAILAND :
		//strTemp << "{call UpdateTestChaNumDecrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call UpdateTestChaNumDecrease(%d, ?)}", nUserNum );
		break;
	case SP_GLOBAL:
		//strTemp << "{call Gsp_UpdateTestChaNumDecrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Gsp_UpdateTestChaNumDecrease(%d, ?)}", nUserNum );
		break;
	case SP_GS :
		//strTemp << "{call UpdateTestChaNumDecrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call GS_UpdateTestChaNumDecrease(%d, ?)}", nUserNum );
		break;
	default :
		//strTemp << "{call UpdateTestChaNumDecrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call UpdateTestChaNumDecrease(%d, ?)}", nUserNum );
		break;
	} // switch (nServiceProvider)

	int nReturn = m_pUserDB->ExecuteSpInt(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

	return nReturn;
}

/**
 * ĳ���� ����, ĳ���ͻ��� ���� ����
 */
int COdbcManager::UserChaNumIncrease(int nUserNum)
{
	if (nUserNum <= 0) return DB_ERROR;

	//std::strstream strTemp;

	TCHAR szTemp[128] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "{call Daum_UpdateChaNumIncrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Daum_UpdateChaNumIncrease(%d, ?)}", nUserNum );
		break;
	case SP_JAPAN : 
		//strTemp << "{call Excite_UpdateChaNumIncrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		//_snprintf( szTemp, 128, "{call Excite_UpdateChaNumIncrease(%d, ?)}", nUserNum );
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 128, "{call Japan_UpdateChaNumIncrease(%d, ?)}", nUserNum );
		break;
	case SP_TERRA : 
		//strTemp << "{call Terra_UpdateChaNumIncrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Terra_UpdateChaNumIncrease(%d, ?)}", nUserNum );
		break;
	case SP_MALAYSIA : 
		//strTemp << "{call Terra_UpdateChaNumIncrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Terra_UpdateChaNumIncrease(%d, ?)}", nUserNum );
		break;
	case SP_THAILAND :
		//strTemp << "{call UpdateChaNumIncrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call UpdateChaNumIncrease(%d, ?)}", nUserNum );
		break;
	case SP_GLOBAL: 
		//strTemp << "{call Gsp_UpdateChaNumIncrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Gsp_UpdateChaNumIncrease(%d, ?)}", nUserNum );
		break;
	case SP_GS :
		//strTemp << "{call UpdateChaNumIncrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call GS_UpdateChaNumIncrease(%d, ?)}", nUserNum );
		break;
	default :
		//strTemp << "{call UpdateChaNumIncrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call UpdateChaNumIncrease(%d, ?)}", nUserNum );
		break;
	} // switch (nServiceProvider)

	int nReturn = m_pUserDB->ExecuteSpInt(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

	return nReturn;
}

/**
 * TEST ���� ĳ���� ����, ���� ����
 */
int COdbcManager::UserTestChaNumIncrease(int nUserNum)
{
	if (nUserNum <= 0) return DB_ERROR;	

	//std::strstream strTemp;
	TCHAR szTemp[128] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "{call Daum_UpdateTestChaNumIncrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Daum_UpdateTestChaNumIncrease(%d, ?)}", nUserNum );
		break;
	case SP_JAPAN :
		//strTemp << "{call Excite_UpdateTestChaNumIncrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		//_snprintf( szTemp, 128, "{call Excite_UpdateTestChaNumIncrease(%d, ?)}", nUserNum );
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 128, "{call Japan_UpdateTestChaNumIncrease(%d, ?)}", nUserNum );
		break;
	case SP_TERRA :
		//strTemp << "{call Terra_UpdateTestChaNumIncrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Terra_UpdateTestChaNumIncrease(%d, ?)}", nUserNum );
		break;
	case SP_MALAYSIA :
		//strTemp << "{call Terra_UpdateTestChaNumIncrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Terra_UpdateTestChaNumIncrease(%d, ?)}", nUserNum );
		break;
	case SP_THAILAND :
		//strTemp << "{call UpdateTestChaNumIncrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call UpdateTestChaNumIncrease(%d, ?)}", nUserNum );
		break;
	case SP_GLOBAL:
		//strTemp << "{call Gsp_UpdateTestChaNumIncrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call Gsp_UpdateTestChaNumIncrease(%d, ?)}", nUserNum );
		break;
	case SP_GS :
		//strTemp << "{call UpdateTestChaNumIncrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call GS_UpdateTestChaNumIncrease(%d, ?)}", nUserNum );
		break;
	default :
		//strTemp << "{call UpdateTestChaNumIncrease(";
		//strTemp << nUserNum ;
		//strTemp << ", ?)}";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "{call UpdateTestChaNumIncrease(%d, ?)}", nUserNum );
		break;
	} // switch (nServiceProvider)

	int nReturn = m_pUserDB->ExecuteSpInt(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

	return nReturn;
}

/**
* �ش����ڸ� ��Ͻ�Ų��.
* \param nUserNum ����ڹ�ȣ
* \param nHour �ð�
* \return 
*/
int COdbcManager::UserBlockDate(
	int nUserNum,
	__time64_t tBlockTime )
{
    if ( nUserNum < 0 || 
		 tBlockTime == 0 )
    {
        Print(_T("UserBlockDate:nUserNum or nHour is incorrect"));
        return DB_ERROR;
    }

	CTime cTemp( tBlockTime );
	CString strTime = cTemp.Format(_T("%Y-%m-%d %H:%M:%S"));

	//std::strstream strTemp;
	TCHAR szTemp[256] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "UPDATE DaumUserInfo SET UserBlock=1,";
		//strTemp << "UserBlockDate='" << strTime.GetString() << "'" ;
		//strTemp << " WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE DaumUserInfo SET UserBlock=1,UserBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	case SP_JAPAN :
		//strTemp << "UPDATE ExciteUserInfo SET UserBlock=1,";
		//strTemp << "UserBlockDate='" << strTime.GetString() << "'" ;
		//strTemp << " WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		//_snprintf( szTemp, 256, "UPDATE ExciteUserInfo SET UserBlock=1,UserBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 256, "UPDATE JapanUserInfo SET UserBlock=1,UserBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	case SP_TERRA :
		//strTemp << "UPDATE TerraUserInfo SET UserBlock=1,";
		//strTemp << "UserBlockDate='" << strTime.GetString() << "'" ;
		//strTemp << " WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE TerraUserInfo SET UserBlock=1,UserBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	case SP_MALAYSIA :
		//strTemp << "UPDATE TerraUserInfo SET UserBlock=1,";
		//strTemp << "UserBlockDate='" << strTime.GetString() << "'" ;
		//strTemp << " WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE TerraUserInfo SET UserBlock=1,UserBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	case SP_THAILAND :
		//strTemp << "UPDATE UserInfo SET UserBlock=1,";
		//strTemp << "UserBlockDate='" << strTime.GetString() << "'" ;
		//strTemp << " WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE UserInfo SET UserBlock=1,UserBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	case SP_GLOBAL:
		//strTemp << "UPDATE GspUserInfo SET UserBlock=1,";
		//strTemp << "UserBlockDate='" << strTime.GetString() << "'" ;
		//strTemp << " WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE GspUserInfo SET UserBlock=1,UserBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	case SP_GS :
		//strTemp << "UPDATE UserInfo SET UserBlock=1,";
		//strTemp << "UserBlockDate='" << strTime.GetString() << "'" ;
		//strTemp << " WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE GSUserInfo SET UserBlock=1,UserBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	default :
		//strTemp << "UPDATE UserInfo SET UserBlock=1,";
		//strTemp << "UserBlockDate='" << strTime.GetString() << "'" ;
		//strTemp << " WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE UserInfo SET UserBlock=1,UserBlockDate='%s' WHERE UserNum=%d", strTime.GetString(), nUserNum );
		break;
	} // switch (nServiceProvider)

	int nReturn = m_pUserDB->ExecuteSQL(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

	return nReturn;
}

/**
 * �ش����ڸ� ��Ͻ�Ų��.
 * �Է�
 * nUserNum : ����ڹ�ȣ
 * nDay : ����ϼ� 1 - 15 ����
 */
int COdbcManager::UserBlock(
	int nUserNum,
	int nDay )
{
    if (nUserNum < 0 || nDay < 0 || nDay > 15)
    {
        Print(_T("UserBlock:nUserNum or nDay is incorrect"));
        return DB_ERROR;
    } // if (nUserNum < 0 || nDay < 0 || nDay > 15)	

	//std::strstream strTemp;
	TCHAR szTemp[256] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "UPDATE DaumUserInfo SET UserBlock=1,";
		//strTemp << "UserBlockDate=getdate()+" << nDay ;
		//strTemp << " WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE DaumUserInfo SET UserBlock=1,UserBlockDate=CURRENT_TIMESTAMP + interval '%d days' WHERE UserNum=%d", nDay, nUserNum );
		break;
	case SP_JAPAN :
		//strTemp << "UPDATE ExciteUserInfo SET UserBlock=1,";
		//strTemp << "UserBlockDate=getdate()+" << nDay ;
		//strTemp << " WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		//_snprintf( szTemp, 256, "UPDATE ExciteUserInfo SET UserBlock=1,UserBlockDate=CURRENT_TIMESTAMP + interval '%d days' WHERE UserNum=%d", nDay, nUserNum );
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 256, "UPDATE JapanUserInfo SET UserBlock=1,UserBlockDate=CURRENT_TIMESTAMP + interval '%d days' WHERE UserNum=%d", nDay, nUserNum );
		break;
	case SP_TERRA :
		//strTemp << "UPDATE TerraUserInfo SET UserBlock=1,";
		//strTemp << "UserBlockDate=getdate()+" << nDay ;
		//strTemp << " WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE TerraUserInfo SET UserBlock=1,UserBlockDate=CURRENT_TIMESTAMP + interval '%d days' WHERE UserNum=%d", nDay, nUserNum );
		break;
	case SP_MALAYSIA :
		//strTemp << "UPDATE TerraUserInfo SET UserBlock=1,";
		//strTemp << "UserBlockDate=getdate()+" << nDay ;
		//strTemp << " WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE TerraUserInfo SET UserBlock=1,UserBlockDate=CURRENT_TIMESTAMP + interval '%d days' WHERE UserNum=%d", nDay, nUserNum );
		break;
	case SP_THAILAND :
		//strTemp << "UPDATE UserInfo SET UserBlock=1,";
		//strTemp << "UserBlockDate=getdate()+" << nDay ;
		//strTemp << " WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE UserInfo SET UserBlock=1,UserBlockDate=CURRENT_TIMESTAMP + interval '%d days' WHERE UserNum=%d", nDay, nUserNum );
		break;
	case SP_GLOBAL:
		//strTemp << "UPDATE GspUserInfo SET UserBlock=1,";
		//strTemp << "UserBlockDate=getdate()+" << nDay ;
		//strTemp << " WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE GspUserInfo SET UserBlock=1,UserBlockDate=CURRENT_TIMESTAMP + interval '%d days' WHERE UserNum=%d", nDay, nUserNum );
		break;
	case SP_GS :
		//strTemp << "UPDATE UserInfo SET UserBlock=1,";
		//strTemp << "UserBlockDate=getdate()+" << nDay ;
		//strTemp << " WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE GSUserInfo SET UserBlock=1,UserBlockDate=CURRENT_TIMESTAMP + interval '%d days' WHERE UserNum=%d", nDay, nUserNum );
		break;
	default :
		//strTemp << "UPDATE UserInfo SET UserBlock=1,";
		//strTemp << "UserBlockDate=getdate()+" << nDay ;
		//strTemp << " WHERE UserNum=";
		//strTemp << nUserNum;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE UserInfo SET UserBlock=1,UserBlockDate=CURRENT_TIMESTAMP + interval '%d days' WHERE UserNum=%d", nDay, nUserNum );
		break;
	} // switch (nServiceProvider)

	int nReturn = m_pUserDB->ExecuteSQL(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

	return nReturn;
}

/**
 * �ش� ���Ӽ����� ��ü ������ �α׾ƿ� ��Ų��.
 */
int	COdbcManager::AllServerUserLogout(int nSvrGrp)
{
	//std::strstream strTemp;
	TCHAR szTemp[256] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "UPDATE DaumUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=";
		//strTemp << nSvrGrp;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE DaumUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d", nSvrGrp );
		break;
	case SP_JAPAN :
		//strTemp << "UPDATE ExciteUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=";
		//strTemp << nSvrGrp;
		//strTemp << std::ends;
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 256, "UPDATE JapanUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d", nSvrGrp );
		break;
	case SP_TERRA :
		//strTemp << "UPDATE TerraUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=";
		//strTemp << nSvrGrp;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE TerraUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d", nSvrGrp );
		break;
	case SP_MALAYSIA :
		//strTemp << "UPDATE TerraUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=";
		//strTemp << nSvrGrp;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE TerraUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d", nSvrGrp );
		break;
	case SP_THAILAND :
		//strTemp << "UPDATE UserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=";
		//strTemp << nSvrGrp;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE UserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d", nSvrGrp );
		break;
	case SP_GLOBAL:
		//strTemp << "UPDATE GspUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=";
		//strTemp << nSvrGrp;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE GspUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d", nSvrGrp );
		break;
	case SP_GS :
		//strTemp << "UPDATE UserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=";
		//strTemp << nSvrGrp;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE GSUserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d", nSvrGrp );
		break;
	default :
		//strTemp << "UPDATE UserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=";
		//strTemp << nSvrGrp;
		//strTemp << std::ends;
		_snprintf( szTemp, 256, "UPDATE UserInfo SET UserLoginState=0 WHERE UserLoginState=1 AND SGNum=%d", nSvrGrp );
		break;
	}

	int nReturn = m_pUserDB->ExecuteSQL(szTemp);
	if ( nReturn != DB_OK ){
		return nReturn;
	}
	
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

    return DB_OK;
}

/**
 * ��ü ������ �α׾ƿ� ��Ų��.
 */
int	COdbcManager::AllUserLogout()
{
	//std::strstream strTemp;
	TCHAR szTemp[128] = {0};

	switch (m_nServiceProvider)
	{
	case SP_KOREA :
		//strTemp << "UPDATE DaumUserInfo SET UserLoginState=0 WHERE UserLoginState=1";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "UPDATE DaumUserInfo SET UserLoginState=0 WHERE UserLoginState=1" );
		break;
	case SP_JAPAN :				
		//_snprintf( szTemp, 128, "UPDATE ExciteUserInfo SET UserLoginState=0 WHERE UserLoginState=1" );
		// �Ϻ� Excite���� Gonzo�� ���񽺻� ���濡 ���� ����
		_snprintf( szTemp, 128, "UPDATE JapanUserInfo SET UserLoginState=0 WHERE UserLoginState=1" );
		break;
	case SP_TERRA :
		//strTemp << "UPDATE TerraUserInfo SET UserLoginState=0 WHERE UserLoginState=1";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "UPDATE TerraUserInfo SET UserLoginState=0 WHERE UserLoginState=1" );
		break;
	case SP_MALAYSIA :
		//strTemp << "UPDATE TerraUserInfo SET UserLoginState=0 WHERE UserLoginState=1";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "UPDATE TerraUserInfo SET UserLoginState=0 WHERE UserLoginState=1" );
		break;
	case SP_THAILAND :
		//strTemp << "UPDATE UserInfo SET UserLoginState=0 WHERE UserLoginState=1";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "UPDATE UserInfo SET UserLoginState=0 WHERE UserLoginState=1" );
		break;
	case SP_GLOBAL:
		//strTemp << "UPDATE GspUserInfo SET UserLoginState=0 WHERE UserLoginState=1";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "UPDATE GspUserInfo SET UserLoginState=0 WHERE UserLoginState=1" );
		break;
	case SP_GS :
		//strTemp << "UPDATE UserInfo SET UserLoginState=0 WHERE UserLoginState=1";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "UPDATE GSUserInfo SET UserLoginState=0 WHERE UserLoginState=1" );
		break;
	default :
		//strTemp << "UPDATE UserInfo SET UserLoginState=0 WHERE UserLoginState=1";
		//strTemp << std::ends;
		_snprintf( szTemp, 128, "UPDATE UserInfo SET UserLoginState=0 WHERE UserLoginState=1" );
		break;
	} // switch (nServiceProvider)

	int nReturn = m_pUserDB->ExecuteSQL(szTemp);
	//strTemp.freeze( false );	// Note : std::strstream�� freeze. �� �ϸ� Leak �߻�.

    return nReturn;
}

//dmk14 | 7-5-17 | 2nd money update
int COdbcManager::UpdateMoneyPoint( DWORD dwPremium, CString strUserID )
{
	TCHAR szTemp[128] = {0};
	_snprintf( szTemp, 128, "{call sp_UpdateMoneyPoint(%d,'%s',?)}",
		dwPremium,
		strUserID.GetString() );

	int nReturn = m_pUserDB->ExecuteSpInt(szTemp);
					
    
	return nReturn;
}

//PANDORA - Added by Arwekaj09 to Deduct UserPoint or E-Points
int COdbcManager::DeductMoneyPoint( DWORD dwPremium, CString strUserID )
{
	TCHAR szTemp[128] = {0};
	_snprintf( szTemp, 128, "{call sp_DeductMoneyPoint(%d,'%s',?)}",
		dwPremium,
		strUserID.GetString() );

	int nReturn = m_pUserDB->ExecuteSpInt(szTemp);
					
    
	return nReturn;
}