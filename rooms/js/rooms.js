
document.addEventListener('DOMContentLoaded', function() {
    const filterTabs = document.querySelectorAll('.filter-tab');
    const roomSections = document.querySelectorAll('.rooms-section');

    
    showAllRooms();

    
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const status = this.getAttribute('data-status');

            
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            
            if (status === 'all') {
                showAllRooms();
            } else {
                showRoomsByStatus(status);
            }
        });
    });

    


    function showAllRooms() {
        roomSections.forEach(section => {
            section.classList.remove('hidden');
        });
    }

    


    function showRoomsByStatus(status) {
        roomSections.forEach(section => {
            const sectionStatus = section.getAttribute('data-status');
            if (sectionStatus === status) {
                section.classList.remove('hidden');
            } else {
                section.classList.add('hidden');
            }
        });
    }

    
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const status = this.getAttribute('data-status');
            if (status !== 'all') {
                const targetSection = document.querySelector(`[data-status="${status}"]`);
                if (targetSection) {
                    setTimeout(() => {
                        targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 100);
                }
            } else {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });
});

