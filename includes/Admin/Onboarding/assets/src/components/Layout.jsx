const Layout = ({ children, onSkip }) => {
    return (
        <div className="onboarding-container relative">
            {/* Skip This Step Link - top right with arrow as per Figma */}
            {onSkip && (
                <button
                    onClick={onSkip}
                    className="absolute top-6 right-6 text-gray-700 hover:text-gray-900 text-base font-medium flex items-center gap-2 transition-all duration-200 bg-transparent border-0 cursor-pointer no-underline hover:underline p-0"
                >
                    Skip This Step
                    <svg
                        width="18"
                        height="14"
                        viewBox="0 0 18 14"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M10.6667 1.16699L16.5 7.00033M16.5 7.00033L10.6667 12.8337M16.5 7.00033L1.5 7.00032"
                            stroke="#334155"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>
            )}

            {/* Logo - matches erp-logo from setup.css */}
            <div className="text-center pt-100px pb-50px border-0 m-0">
                <h1 className="m-0 p-0 border-0">
                    {window.wpErpOnboarding?.logoUrl ? (
                        <img
                            src={window.wpErpOnboarding.logoUrl}
                            alt="WP ERP"
                            className="max-w-150px h-auto mx-auto"
                        />
                    ) : (
                        <span className="text-2xl font-bold">
                            <span className="text-blue-800">WP</span>{" "}
                            <span className="text-blue-600">ERP</span>
                        </span>
                    )}
                </h1>
            </div>

            {/* Main Content - children contain their own max-width constraints */}
            <div>{children}</div>
        </div>
    );
};

export default Layout;
