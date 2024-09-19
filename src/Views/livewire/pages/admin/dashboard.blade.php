@php
    @endphp

<div>
    <x-doctalk::admin.sidebarlinks/>

    <div class="page">
        <div class="dashboard">
            <div class="dashboard-box">
                <div class="dashboard-title">Documents</div>
                <div class="dashboard-number">
                    {{number_format($this->stats['documents'], 0, ',')}}
                </div>
            </div>
            <div class="dashboard-box">
                <div class="dashboard-title">Conversations</div>
                <div class="dashboard-number">
                    {{number_format($this->stats['conversations'], 0, ',')}}
                </div>
            </div>
            <div class="dashboard-box">
                <div class="dashboard-title">Users</div>
                <div class="dashboard-number">
                    {{number_format($this->stats['users'], 0, ',')}}
                </div>
            </div>
            <div class="dashboard-box">
                <div class="dashboard-title">Messages</div>
                <div class="dashboard-number">
                    {{number_format($this->stats['messages'], 0, ',')}}
                </div>
            </div>
        </div>
    </div>

    <style>
        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            width: 100%;
            gap: 20px;
        }

        .dashboard-box {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .dashboard-title {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .dashboard-number {
            font-size: 48px;
            font-weight: 700;
            color: #555;
        }
    </style>

</div>
