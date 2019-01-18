(function($) {
    $.fn.datePreset = function($startDate, $endDate) {
        var formatDate = function(dateObj) {
            var year = dateObj.getFullYear();
            var month = dateObj.getMonth() + 1;
            var date = dateObj.getDate();
            if (date < 10) {
                date = "0" + date;
            }
            if (month < 10) {
                month = "0" + month;
            }
            return year + "-" + month + "-" + date;
        };

        $(this).click(function(e) {
            e.preventDefault();
            var preset = $(this).data('preset');
            var startDateObj, endDateObj;
            var startDateString, endDateString;
            var todayObj = new Date;
            switch (preset) {
                case 'today':
                    startDateObj = todayObj;
                endDateObj = todayObj;
                break;
                case 'yesterday':
                    startDateObj = new Date(todayObj.getFullYear(), todayObj.getMonth(), todayObj.getDate() - 1);
                endDateObj = new Date(todayObj.getFullYear(), todayObj.getMonth(), todayObj.getDate() - 1);
                break;
                case 'this-month':
                    startDateObj = new Date(todayObj.getFullYear(), todayObj.getMonth(), 1);
                endDateObj = todayObj;
                break;
                case 'last-month':
                    startDateObj = new Date(todayObj.getFullYear(), todayObj.getMonth() - 1, 1);
                endDateObj = new Date(todayObj.getFullYear(), todayObj.getMonth(), 0);
                break;
                case 'this-year':
                    startDateObj = new Date(todayObj.getFullYear(), 0, 1);
                endDateObj = todayObj;
                break;
                case 'last-year':
                    startDateObj = new Date(todayObj.getFullYear() - 1, 0, 1);
                endDateObj = new Date(todayObj.getFullYear(), 0, 0);
                break;
                case 'recent-7-days':
                    startDateObj = new Date(todayObj.getFullYear(), todayObj.getMonth(), todayObj.getDate() - 7);
                endDateObj = todayObj;
                break;
                case 'recent-30-days':
                    startDateObj = new Date(todayObj.getFullYear(), todayObj.getMonth(), todayObj.getDate() - 30);
                endDateObj = todayObj;
                break;
                case 'recent-12-months':
                    startDateObj = new Date(todayObj.getFullYear(), todayObj.getMonth() - 12, 1);
                endDateObj = todayObj;
                break;
            }
            startDateString = formatDate(startDateObj);
            endDateString = formatDate(endDateObj);
            $startDate.val(startDateString);
            $endDate.val(endDateString);
        });
    };
})(jQuery);

