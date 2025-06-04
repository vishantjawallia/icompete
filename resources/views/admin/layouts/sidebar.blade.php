<div class="deznav">
    <div class="deznav-scroll">
        <ul class="metismenu" id="menu">
            <li class="menu-title">Home</li>
            <li>
                <a href="{{ route('admin.index') }}" class="menu-link waves-effect">
                    <div class="menu-icon">
                        <i class="fad fa-home"></i>
                    </div>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.staffs') }}" class="menu-link waves-effect">
                    <div class="menu-icon">
                        <i class="fad fa-user-lock"></i>
                    </div>
                    <span class="nav-text">Staffs</span>
                </a>
            </li>
            <li class="menu-title">Users</li>
            <li>
                <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="fad fa-user"></i>
                    </div>
                    <span class="nav-text">Users</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.users.index') }}" class="menu-link">All Users</a></li>
                    <li><a href="{{ route('admin.users.contestants') }}" class="menu-link">Contestants</a></li>
                    <li><a href="{{ route('admin.users.voters') }}" class="menu-link">Voters</a></li>
                    <li><a href="{{ route('admin.users.organizers') }}" class="menu-link">Organizers</a></li>
                    <li><a href="{{ route('admin.users.banned') }}" class="menu-link">Banned Users</a></li>
                    <li><a href="{{ route('admin.users.settings') }}" class="menu-link">Settings</a></li>
                </ul>
            </li>

            <li class="menu-title">Contests</li>
            <li>
                <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="fad fa-trophy"></i>
                    </div>
                    <span class="nav-text">Contests</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{route('admin.contest.index')}}" class="menu-link">All Contests</a></li>
                    <li><a href="{{route('admin.category.index')}}" class="menu-link">Categories</a></li>
                    <li><a href="{{route('admin.submission.index')}}" class="menu-link">Submissions</a></li>
                    <li><a href="{{route('admin.submission.entry')}}" class="menu-link">Participants</a></li>
                    <li><a href="{{route('admin.contest.settings')}}" class="menu-link">Contests Settings</a></li>
                </ul>
            </li>

            <li>
                <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="fad fa-coins"></i>
                    </div>
                    <span class="nav-text">Coins</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{route('admin.coin.transactions')}}" class="menu-link">All Transactions</a></li>
                    <li><a href="{{route('admin.coin.settings')}}" class="menu-link">Settings</a></li>
                </ul>
            </li>

            <li>
                <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="fad fa-chart-bar"></i>
                    </div>
                    <span class="nav-text">Voting</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{route('admin.reports.votes')}}" class="menu-link">History</a></li>
                    {{-- <li><a href="#" class="menu-link">Reports (Money Received, etc)</a></li> --}}
                </ul>
            </li>

            <li>
                <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="fad fa-comments"></i>
                    </div>
                    <span class="nav-text">Community Feeds</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{route('admin.community.posts')}}" class="menu-link">All Posts</a></li>
                    <li><a href="{{route('admin.community.comments')}}" class="menu-link">Comments</a></li>
                    <li><a href="{{route('admin.community.settings')}}" class="menu-link">Settings</a></li>
                </ul>
            </li>

            <li class="menu-title">Reports</li>
            <li>
                <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="fad fa-dollar-sign"></i>
                    </div>
                    <span class="nav-text">Withdrawal</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.withdrawal.pending') }}" class="menu-link">Pending Withdrawals</a></li>
                    <li><a href="{{ route('admin.withdrawal.history') }}" class="menu-link">Withdrawal History</a></li>
                    <li><a href="{{ route('admin.withdrawal.settings') }}" class="menu-link">Withdrawal Settings</a></li>
                </ul>
            </li>

            <li class="menu-title">Reports and Logs</li>
            <li>
                <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="fad fa-file-alt"></i>
                    </div>
                    <span class="nav-text">Reports</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.reports.login.history') }}" class="menu-link">Login History</a></li>
                    {{-- <li><a href="{{ route('admin.reports.referrals') }}" class="menu-link">Referrals</a></li> --}}
                    <li><a href="{{ route('admin.reports.notifications') }}" class="menu-link">Notifications</a></li>
                </ul>
            </li>

            <li class="menu-title">Notifications</li>
            <li>
                <a href="{{ route('admin.newsletter.index') }}" class="menu-link waves-effect">
                    <div class="menu-icon">
                        <i class="fad fa-envelope"></i>
                    </div>
                    <span class="nav-text">Newsletter</span>
                </a>
            </li>
            <li>
                <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="fad fa-folder-gear"></i>
                    </div>
                    <span class="nav-text">Templates</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.email.templates.index') }}" class="menu-link">Email Templates</a></li>
                    <li><a href="{{ route('admin.notify.templates.index') }}" class="menu-link">APP Notifications</a></li>
                </ul>
            </li>

            <li class="menu-title">System</li>
            <li>
                <a href="{{ route('admin.settings.payment') }}" class="menu-link waves-effect">
                    <div class="menu-icon">
                        <i class="fad fa-money-bill-wave"></i>
                    </div>
                    <span class="nav-text">Payment Settings</span>
                </a>
            </li>
            <li>
                <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="fad fa-cogs"></i>
                    </div>
                    <span class="nav-text">Site Settings</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.settings.index') }}" class="menu-link">General Settings</a></li>
                    <li><a href="{{ route('admin.settings.email') }}" class="menu-link">Email Settings</a></li>
                    {{-- <li><a href="{{ route('admin.settings.system') }}" class="menu-link">System Settings</a></li> --}}
                </ul>
            </li>

            <li>
                <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="fad fa-server"></i>
                    </div>
                    <span class="nav-text">System</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.system.cache') }}" class="menu-link">Clear Cache</a></li>
                    <li><a href="{{ route('admin.system.server') }}" class="menu-link">Server Info</a></li>
                </ul>
            </li>
        </ul>
        <div class="copyright mb-3">
            <p>{{get_setting('title')}} © <span class="current-year">2024</span> All Rights Reserved</p>
            <p>Built by <a class="text-secondary" href="https://jadesdev.com.ng/" target="_blank">Jadesdev</a> </p>
        </div>
    </div>
</div>
<style>


</style>
