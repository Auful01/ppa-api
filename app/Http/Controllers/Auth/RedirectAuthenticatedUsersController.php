<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RedirectAuthenticatedUsersController extends Controller
{
    public function home(Request $request)
    {
        if (auth()->user()->role == 'ict_developer' && auth()->user()->site == 'BIB' || auth()->user()->role == 'ict_ho' && auth()->user()->site == 'HO' || auth()->user()->role == 'ict_bod' && auth()->user()->site == 'HO') {
                return redirect('dashboard');
        } elseif (auth()->user()->role == 'ict_technician' && auth()->user()->site == 'BIB' || auth()->user()->role ==  'ict_group_leader' && auth()->user()->site == 'BIB' || auth()->user()->role ==  'ict_admin' && auth()->user()->site == 'BIB') {
            return redirect()->route('dashboardBib.page');
        } elseif (auth()->user()->role == 'ict_technician' && auth()->user()->site == 'BA' || auth()->user()->role ==  'ict_group_leader' && auth()->user()->site == 'BA' || auth()->user()->role ==  'ict_admin' && auth()->user()->site == 'BA') {
            return redirect()->route('dashboardBa.page');
        } elseif (auth()->user()->role == 'ict_technician' && auth()->user()->site == 'MIFA' || auth()->user()->role ==  'ict_group_leader' && auth()->user()->site == 'MIFA' || auth()->user()->role ==  'ict_admin' && auth()->user()->site == 'MIFA') {
            return redirect()->route('dashboardMifa.page');
        } elseif (auth()->user()->role == 'ict_technician' && auth()->user()->site == 'MHU' || auth()->user()->role ==  'ict_group_leader' && auth()->user()->site == 'MHU' || auth()->user()->role ==  'ict_admin' && auth()->user()->site == 'MHU') {
            return redirect()->route('dashboardMhu.page');
        } elseif (auth()->user()->role == 'ict_technician' && auth()->user()->site == 'AMI' || auth()->user()->role ==  'ict_group_leader' && auth()->user()->site == 'AMI' || auth()->user()->role ==  'ict_admin' && auth()->user()->site == 'AMI') {
            return redirect()->route('dashboardAmi.page');
        } elseif (auth()->user()->role == 'ict_technician' && auth()->user()->site == 'ADW' || auth()->user()->role ==  'ict_group_leader' && auth()->user()->site == 'ADW' || auth()->user()->role ==  'ict_admin' && auth()->user()->site == 'ADW') {
            return redirect()->route('dashboardWara.page');
        } elseif (auth()->user()->role == 'ict_technician' && auth()->user()->site == 'PIK' || auth()->user()->role ==  'ict_group_leader' && auth()->user()->site == 'PIK' || auth()->user()->role ==  'ict_admin' && auth()->user()->site == 'PIK') {
            return redirect()->route('dashboardPik.page');
        } elseif (auth()->user()->role == 'ict_technician' && auth()->user()->site == 'IPT' || auth()->user()->role ==  'ict_group_leader' && auth()->user()->site == 'IPT' || auth()->user()->role ==  'ict_admin' && auth()->user()->site == 'IPT') {
            return redirect()->route('dashboardIpt.page');
        } elseif (auth()->user()->role == 'ict_technician' && auth()->user()->site == 'MLP' || auth()->user()->role ==  'ict_group_leader' && auth()->user()->site == 'MLP' || auth()->user()->role ==  'ict_admin' && auth()->user()->site == 'MLP') {
            return redirect()->route('dashboardMlp.page');
        } elseif (auth()->user()->role == 'ict_technician' && auth()->user()->site == 'MIP' || auth()->user()->role ==  'ict_group_leader' && auth()->user()->site == 'MIP' || auth()->user()->role ==  'ict_admin' && auth()->user()->site == 'MIP') {
            return redirect()->route('dashboardMip.page');
        } elseif (auth()->user()->role == 'ict_technician' && auth()->user()->site == 'VIB' || auth()->user()->role ==  'ict_group_leader' && auth()->user()->site == 'VIB' || auth()->user()->role ==  'ict_admin' && auth()->user()->site == 'VIB') {
            return redirect()->route('dashboardVale.page');
        } elseif (auth()->user()->role == 'ict_technician' && auth()->user()->site == 'SBS' || auth()->user()->role ==  'ict_group_leader' && auth()->user()->site == 'SBS' || auth()->user()->role ==  'ict_admin' && auth()->user()->site == 'SBS') {
            return redirect()->route('dashboardSbs.page');
        } elseif (auth()->user()->role == 'ict_technician' && auth()->user()->site == 'SKS' || auth()->user()->role ==  'ict_group_leader' && auth()->user()->site == 'SKS' || auth()->user()->role ==  'ict_admin' && auth()->user()->site == 'SKS') {
            return redirect()->route('dashboardSks.page');
        } elseif (auth()->user()->role  ==  'ict_group_leader' && auth()->user()->site == 'BGE' || auth()->user()->role == 'ict_technician' && auth()->user()->site == 'BGE') {
            return redirect()->route('dashboardBge.page');
        } elseif (auth()->user()->role == 'soc_ho') {
            return redirect()->route('aduan.page');
        } elseif (auth()->user()->role == 'guest') {
            return redirect('complaint/dashboard');
        } else{
            return redirect('/login');
        }
    }
}
